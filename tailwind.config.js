import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";
import typography from "@tailwindcss/typography";

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: "class",
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        "./node_modules/flowbite/**/*.js",
        "./app/Livewire/**/*Table.php",
        "./vendor/power-components/livewire-powergrid/resources/views/**/*.php",
        "./vendor/power-components/livewire-powergrid/src/Themes/Tailwind.php",
        "./app/PowerGridThemes/Medibranch.php",
        "./vendor/rappasoft/laravel-livewire-tables/resources/views/*.blade.php",
        "./vendor/rappasoft/laravel-livewire-tables/resources/views/**/*.blade.php",
        "./app/Livewire/*.php",
        "./app/Livewire/**/*.php",
    ],

    theme: {
        extend: {
            colors: {
                primary: {
                    DEFAULT: "#1976D2",
                    dark: "#90CAF9",
                },
                secondary: {
                    DEFAULT: "#5C946E",
                    dark: "#5C946E",
                },
                accent: {
                    DEFAULT: "#FFD54F",
                    dark: "#FFEE58",
                },
                background: {
                    DEFAULT: "#F9FAFB",
                    dark: "#1E1E1E",
                },
                surface: {
                    DEFAULT: "#FFFFFF",
                    dark: "#2A2D34",
                },
                border: {
                    DEFAULT: "#E0E0E0",
                    dark: "#444C56",
                },
                text: {
                    DEFAULT: "#1E293B",
                    dark: "#E3EAF5",
                    muted: "#64748B",
                    "muted-dark": "#94A3B8",
                },
                success: {
                    DEFAULT: "#2E7D32",
                    dark: "#66BB6A",
                },
                error: {
                    DEFAULT: "#D32F2F",
                    dark: "#EF5350",
                },
                warning: {
                    DEFAULT: "#FB8C00",
                    dark: "#FFA726",
                },
                info: {
                    DEFAULT: "#0288D1",
                    dark: "#4FC3F7",
                },
                input: {
                    bg: "#FFFFFF",
                    "bg-dark": "#2A2D34",
                    border: "#CBD5E1",
                    "border-dark": "#444C56",
                    text: "#1E293B",
                    "text-dark": "#E3EAF5",
                    placeholder: "#94A3B8",
                },
                button: {
                    bg: "#1976D2",
                    "bg-dark": "#90CAF9",
                    text: "#FFFFFF",
                    "text-dark": "#1E1E1E",
                },
                chip: {
                    primary: {
                        text: {
                            DEFAULT: "#1976D2",
                            dark: "#E3EAF5",
                        },
                        bg: {
                            DEFAULT: "#E3F2FD",
                            dark: "#1565C0",
                        },
                        border: {
                            DEFAULT: "#BBDEFB",
                            dark: "#90CAF9",
                        },
                    },
                    secondary: {
                        text: {
                            DEFAULT: "#5C946E",
                            dark: "#E3EAF5",
                        },
                        bg: {
                            DEFAULT: "#E8F5E9",
                            dark: "#446F5A",
                        },
                        border: {
                            DEFAULT: "#A5D6A7",
                            dark: "#5C946E",
                        },
                    },
                    accent: {
                        text: {
                            DEFAULT: "#FB8C00",
                            dark: "#1E1E1E",
                        },
                        bg: {
                            DEFAULT: "#FFF8E1",
                            dark: "#FFECB3",
                        },
                        border: {
                            DEFAULT: "#FFD54F",
                            dark: "#FFEE58",
                        },
                    },
                    success: {
                        text: {
                            DEFAULT: "#2E7D32",
                            dark: "#E3EAF5",
                        },
                        bg: {
                            DEFAULT: "#E8F5E9",
                            dark: "#1B5E20",
                        },
                        border: {
                            DEFAULT: "#A5D6A7",
                            dark: "#66BB6A",
                        },
                    },
                    error: {
                        text: {
                            DEFAULT: "#D32F2F",
                            dark: "#E3EAF5",
                        },
                        bg: {
                            DEFAULT: "#FFEBEE",
                            dark: "#C62828",
                        },
                        border: {
                            DEFAULT: "#EF9A9A",
                            dark: "#EF5350",
                        },
                    },
                    warning: {
                        text: {
                            DEFAULT: "#FB8C00",
                            dark: "#1E1E1E",
                        },
                        bg: {
                            DEFAULT: "#FFF3E0",
                            dark: "#EF6C00",
                        },
                        border: {
                            DEFAULT: "#FFCC80",
                            dark: "#FFA726",
                        },
                    },
                    info: {
                        text: {
                            DEFAULT: "#0288D1",
                            dark: "#E3EAF5",
                        },
                        bg: {
                            DEFAULT: "#E1F5FE",
                            dark: "#0277BD",
                        },
                        border: {
                            DEFAULT: "#81D4FA",
                            dark: "#4FC3F7",
                        },
                    },
                    neutral: {
                        text: {
                            DEFAULT: "#64748B",
                            dark: "#94A3B8",
                        },
                        bg: {
                            DEFAULT: "#F1F5F9",
                            dark: "#2A2D34",
                        },
                        border: {
                            DEFAULT: "#E0E0E0",
                            dark: "#444C56",
                        },
                    },
                },
            },
            fontFamily: {
                sans: ["Inter", ...defaultTheme.fontFamily.sans],
                bengali: ["Anek Bangla", "sans-serif"],
            },
            fontSize: {
                base: "16px",
                xxs: "10px",
                xs: "12px",
                sm: "14px",
                lg: "18px",
                xl: "20px",
                "2xl": "24px",
                "3xl": "30px",
            },
            lineHeight: {
                normal: "1.5",
                relaxed: "1.75",
                loose: "2",
            },
            fontWeight: {
                normal: "400",
                medium: "500",
                semibold: "600",
                bold: "700",
            },
        },
    },

    plugins: [forms, typography, require("flowbite/plugin")],
};
