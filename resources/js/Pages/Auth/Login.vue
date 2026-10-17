<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Eye, EyeOff, MailCheck } from '@lucide/vue';
import { nextTick, onMounted, ref } from 'vue';
import BrandStripe from '@/Components/Layout/BrandStripe.vue';
import Toast from '@/Components/Toast.vue';
import { useToasts } from '@/Composables/useToasts';
import { cn } from '@/lib/utils';
import { buttonVariants, inputClass } from '@/lib/variants';

const props = defineProps({
    otpPending: { type: Boolean, default: false },
    otpEmail: { type: String, default: '' },
});

const showPassword = ref(false);
const { push } = useToasts();
const otpDigits = ref(Array(6).fill(''));
const otpInputs = ref([]);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});
const otpForm = useForm({ code: '' });
const resendForm = useForm({});
const cancelForm = useForm({});

const submit = () => {
    form.post(window.location.pathname, {
        onError: (errors) => {
            push(errors.email || errors.password || 'Connexion impossible. Vérifiez vos identifiants puis réessayez.', 'error');
        },
        onFinish: () => form.reset('password'),
    });
};

const submitOtp = () => {
    otpForm.code = otpDigits.value.join('');
    otpForm.post('/me/login/otp', {
        onError: () => { otpDigits.value = Array(6).fill(''); nextTick(() => otpInputs.value[0]?.focus()); },
    });
};

const focusOtp = (index) => nextTick(() => otpInputs.value[index]?.focus());
const handleOtpInput = (index, event) => {
    const digits = event.target.value.replace(/\D/g, '');
    if (digits.length > 1) {
        [...digits].slice(0, 6 - index).forEach((digit, offset) => { otpDigits.value[index + offset] = digit; });
        focusOtp(Math.min(index + digits.length, 5));
    } else {
        otpDigits.value[index] = digits;
        if (digits && index < 5) focusOtp(index + 1);
    }
    otpForm.clearErrors('code');
};
const handleOtpKeydown = (index, event) => {
    if (event.key === 'Backspace' && !otpDigits.value[index] && index > 0) focusOtp(index - 1);
    if (event.key === 'ArrowLeft' && index > 0) { event.preventDefault(); focusOtp(index - 1); }
    if (event.key === 'ArrowRight' && index < 5) { event.preventDefault(); focusOtp(index + 1); }
};
const handleOtpPaste = (event) => {
    const digits = event.clipboardData?.getData('text')?.replace(/\D/g, '').slice(0, 6) ?? '';
    if (!digits) return;
    event.preventDefault();
    otpDigits.value = Array.from({ length: 6 }, (_, index) => digits[index] ?? '');
    focusOtp(Math.min(digits.length, 5));
};

onMounted(() => {
    if (props.otpPending) focusOtp(0);
});
</script>

<template>
    <Head title="Connexion"><meta name="robots" content="noindex, nofollow, noarchive"></Head>
    <Toast />

    <main id="main" class="flex min-h-dvh flex-col bg-muted">
        <BrandStripe />
        <div class="flex flex-1 flex-col items-center justify-center gap-6 px-4 py-10">
            <div class="w-full max-w-sm rounded-xl border bg-card p-6 shadow-sm sm:p-8">
                <Link href="/" class="flex items-center gap-2.5 rounded-md outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50">
                    <span class="grid size-9 place-items-center rounded-lg bg-eden-600 text-white" aria-hidden="true">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21V9" /><path d="M12 12.5C12 8.5 9 6.2 4.5 6.2c0 4 3 6.3 7.5 6.3Z" /><path d="M12 10c0-3.6 3-5.8 7.5-5.8 0 3.6-3 5.8-7.5 5.8Z" /></svg>
                    </span>
                    <span class="grid leading-tight">
                        <span class="text-sm font-bold">EDEN</span>
                        <span class="text-xs text-muted-foreground">École Doctorale Écosystèmes Naturels</span>
                    </span>
                </Link>

                <div class="mt-7">
                    <h1 class="text-2xl font-semibold tracking-tight">{{ otpPending ? 'Vérification en deux étapes' : 'Connexion' }}</h1>
                    <p class="mt-1.5 text-sm text-muted-foreground">{{ otpPending ? 'Saisissez le code à 6 chiffres envoyé à votre adresse e-mail.' : 'Espace réservé à l’équipe qui gère le site.' }}</p>
                </div>

                <form v-if="!otpPending" class="mt-6 grid gap-4" novalidate @submit.prevent="submit">
                    <p v-if="form.errors.code" class="rounded-md border border-destructive/30 bg-destructive/5 px-3 py-2 text-sm text-destructive" role="alert">{{ form.errors.code }}</p>
                    <div class="grid gap-2">
                        <label class="text-sm font-medium" for="email">Adresse e-mail</label>
                        <input id="email" v-model="form.email" :class="inputClass" type="email" autocomplete="email" required autofocus placeholder="vous@exemple.mg" :aria-invalid="form.errors.email ? 'true' : null" aria-describedby="email-error">
                        <p v-if="form.errors.email" id="email-error" class="text-xs font-medium text-destructive" role="alert">{{ form.errors.email }}</p>
                    </div>

                    <div class="grid gap-2">
                        <label class="text-sm font-medium" for="password">Mot de passe</label>
                        <div class="relative">
                            <input id="password" v-model="form.password" :class="cn(inputClass, 'pr-11')" :type="showPassword ? 'text' : 'password'" autocomplete="current-password" required :aria-invalid="form.errors.password ? 'true' : null" aria-describedby="password-error">
                            <button
                                type="button"
                                :class="cn(buttonVariants({ variant: 'ghost', size: 'icon-sm' }), 'absolute top-1/2 right-1 -translate-y-1/2 text-muted-foreground')"
                                :aria-label="showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
                                :aria-pressed="showPassword ? 'true' : 'false'"
                                @click="showPassword = !showPassword"
                            >
                                <EyeOff v-if="showPassword" aria-hidden="true" />
                                <Eye v-else aria-hidden="true" />
                            </button>
                        </div>
                        <p v-if="form.errors.password" id="password-error" class="text-xs font-medium text-destructive" role="alert">{{ form.errors.password }}</p>
                    </div>

                    <label class="inline-flex cursor-pointer items-center gap-2 text-sm">
                        <input v-model="form.remember" type="checkbox" class="size-4 accent-eden-600">
                        Rester connecté sur cet appareil
                    </label>

                    <button :class="cn(buttonVariants(), 'mt-2 w-full')" type="submit" :disabled="form.processing">
                        <span v-if="form.processing" class="size-4 animate-spin rounded-full border-2 border-current border-t-transparent" aria-hidden="true"></span>
                        {{ form.processing ? 'Connexion…' : 'Se connecter' }}
                    </button>
                </form>

                <div v-else class="mt-6 grid gap-5">
                    <div class="flex items-center gap-3 rounded-lg border bg-muted/50 p-3.5">
                        <span class="grid size-9 shrink-0 place-items-center rounded-md bg-primary/10 text-primary"><MailCheck class="size-4" aria-hidden="true" /></span>
                        <p class="min-w-0 text-sm text-muted-foreground">Code envoyé à <strong class="break-all font-medium text-foreground">{{ otpEmail }}</strong></p>
                    </div>

                    <form class="grid gap-5" novalidate @submit.prevent="submitOtp">
                        <fieldset class="grid gap-2.5">
                            <legend class="text-sm font-medium">Code de vérification</legend>
                            <div class="flex justify-between gap-2" dir="ltr" @paste="handleOtpPaste">
                                <input
                                    v-for="(_, index) in otpDigits"
                                    :key="index"
                                    :ref="(element) => { otpInputs[index] = element; }"
                                    v-model="otpDigits[index]"
                                    type="text"
                                    inputmode="numeric"
                                    pattern="[0-9]*"
                                    maxlength="1"
                                    :autocomplete="index === 0 ? 'one-time-code' : 'off'"
                                    :aria-label="`Chiffre ${index + 1} du code`"
                                    :class="cn(inputClass, 'size-11 px-0 text-center text-xl font-semibold tabular-nums sm:size-12', otpForm.errors.code && 'border-destructive ring-[3px] ring-destructive/15')"
                                    required
                                    @input="handleOtpInput(index, $event)"
                                    @keydown="handleOtpKeydown(index, $event)"
                                >
                            </div>
                            <p v-if="otpForm.errors.code" class="text-xs font-medium text-destructive" role="alert">{{ otpForm.errors.code }}</p>
                        </fieldset>
                        <button :class="buttonVariants({ size: 'lg' })" class="w-full" type="submit" :disabled="otpForm.processing || otpDigits.some((digit) => !digit)">
                            <span v-if="otpForm.processing" class="size-4 animate-spin rounded-full border-2 border-current border-t-transparent" aria-hidden="true"></span>
                            Vérifier et se connecter
                        </button>
                    </form>

                    <div class="flex flex-col items-center gap-2 text-sm">
                        <button type="button" class="font-medium text-primary underline-offset-4 hover:underline disabled:opacity-50" :disabled="resendForm.processing" @click="resendForm.post('/me/login/otp/resend')">
                            {{ resendForm.processing ? 'Envoi…' : 'Renvoyer un code' }}
                        </button>
                        <p v-if="resendForm.errors.email || resendForm.errors.code" class="text-center text-xs font-medium text-destructive" role="alert">{{ resendForm.errors.email || resendForm.errors.code }}</p>
                        <button type="button" class="text-muted-foreground underline-offset-4 hover:text-foreground hover:underline" :disabled="cancelForm.processing" @click="cancelForm.post('/me/login/otp/cancel')">Utiliser un autre compte</button>
                    </div>
                </div>
            </div>

            <Link href="/" :class="buttonVariants({ variant: 'ghost', size: 'sm' })"><ArrowLeft aria-hidden="true" />Retour au site</Link>
        </div>
    </main>
</template>
