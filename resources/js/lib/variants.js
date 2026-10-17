import { cva } from 'class-variance-authority';

// Variantes partagées (mêmes recettes que shadcn/ui), utilisables sur <button>, <a>, <Link> ou <label>.
export const buttonVariants = cva(
    "inline-flex shrink-0 items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-[color,background-color,border-color,box-shadow] outline-none select-none focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:pointer-events-none disabled:opacity-50 aria-disabled:pointer-events-none aria-disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4",
    {
        variants: {
            variant: {
                default: 'bg-primary text-primary-foreground shadow-xs hover:bg-eden-700 dark:hover:bg-eden-400',
                outline: 'border border-input bg-background shadow-xs hover:bg-accent hover:text-accent-foreground dark:bg-input/20 dark:hover:bg-input/40',
                secondary: 'bg-secondary text-secondary-foreground hover:bg-accent',
                ghost: 'hover:bg-accent hover:text-accent-foreground',
                link: 'h-auto px-0 text-eden-700 underline-offset-4 hover:underline dark:text-eden-300',
                destructive: 'bg-destructive text-white shadow-xs hover:bg-laterite-700',
                'destructive-ghost': 'text-destructive hover:bg-laterite-50 dark:hover:bg-laterite-950/50',
                inverse: 'bg-white text-eden-900 shadow-xs hover:bg-eden-50',
            },
            size: {
                default: 'h-10 px-4',
                sm: 'h-8 gap-1.5 px-3',
                lg: 'h-11 px-6 text-base',
                icon: 'size-10',
                'icon-sm': 'size-8',
            },
        },
        defaultVariants: { variant: 'default', size: 'default' },
    },
);

export const badgeVariants = cva(
    'inline-flex w-fit shrink-0 items-center gap-1.5 whitespace-nowrap rounded-md border px-2 py-0.5 text-xs font-medium [&_svg]:size-3',
    {
        variants: {
            variant: {
                default: 'border-transparent bg-eden-50 text-eden-700 dark:bg-eden-950 dark:text-eden-300',
                secondary: 'border-transparent bg-secondary text-muted-foreground',
                outline: 'border-border text-foreground',
                success: 'border-transparent bg-eden-50 text-eden-700 dark:bg-eden-950 dark:text-eden-300',
                warning: 'border-transparent bg-gold-50 text-gold-800 dark:bg-gold-950 dark:text-gold-300',
                destructive: 'border-transparent bg-laterite-50 text-laterite-700 dark:bg-laterite-950 dark:text-laterite-300',
                info: 'border-transparent bg-sky-50 text-sky-700 dark:bg-sky-950 dark:text-sky-300',
            },
            dot: { true: "before:size-1.5 before:rounded-full before:bg-current before:content-['']", false: '' },
        },
        defaultVariants: { variant: 'default', dot: false },
    },
);

export const inputClass = 'flex h-10 w-full min-w-0 rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50 aria-invalid:border-destructive aria-invalid:ring-destructive/20 dark:bg-input/20';

export const textareaClass = 'flex min-h-24 w-full rounded-md border border-input bg-background px-3 py-2 text-sm leading-relaxed shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50 aria-invalid:border-destructive dark:bg-input/20';

export const cardClass = 'rounded-xl border bg-card text-card-foreground shadow-xs';

export const containerClass = 'mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8';
