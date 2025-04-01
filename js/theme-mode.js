if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
    document.documentElement.classList.add('theme-dark');
} else {
    document.documentElement.classList.remove('theme-dark');
}
