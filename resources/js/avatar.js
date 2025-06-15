export default function avatarComponent(initialName, colorList) {
    return {
        name: initialName,
        colors: colorList,
        get initials() {
            const words = this.name.trim().split(/\s+/); // handles multiple spaces
            if (words.length === 0) return "";
            if (words.length === 1) return words[0][0]?.toUpperCase() ?? "";
            return (words[0][0] + words[words.length - 1][0]).toUpperCase();
        },
        get bgColor() {
            let hash = 0;
            for (let i = 0; i < this.name.length; i++) {
                hash = this.name.charCodeAt(i) + ((hash << 5) - hash);
            }
            const index = Math.abs(hash) % this.colors.length;
            return this.colors[index];
        },
        update(newName) {
            this.name = newName;
        },
    };
}
