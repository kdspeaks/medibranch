export default () => ({
    dark: localStorage.getItem('theme') === 'dark',
    animating: false,
    toggleTheme() {
        this.animating = true;
        setTimeout(() => {
            this.dark = !this.dark;
            localStorage.setItem('theme', this.dark ? 'dark' : 'light');
        }, 300); // sync with animation
        setTimeout(() => this.animating = false, 300);
    },
    init() {
        this.$watch('dark', val => {
            localStorage.setItem('theme', val ? 'dark' : 'light');
        });
    }
});

