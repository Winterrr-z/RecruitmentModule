/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./app/**/*.php",
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
    "./storage/framework/views/*.php",
    "./node_modules/flowbite/**/*.js"
  ],
  theme: {
    extend: {
      colors: {
        "surface" : '#f7f9fb',
        "surface-dim" : '#d8dadc',
        "surface-bright" : '#f7f9fb',
        "surface-container-lowest" : '#ffffff',
        "surface-container-low" : '#f2f4f6',
        "surface-container" : '#eceef0',
        "surface-container-high" : '#e6e8ea',
        "surface-container-highest" : '#e0e3e5',
        "on-surface" : '#191c1e',
        "on-surface-variant" : '#494454',
        "inverse-surface" : '#2d3133',
        "inverse-on-surface" : '#eff1f3',
        "outline" : '#7b7486',
        "outline-variant" : '#cbc3d7',
        "surface-tint" : '#6d3bd7',
        "primary" : '#6b38d4',
        "on-primary" : '#ffffff',
        "primary-container" : '#8455ef',
        "on-primary-container" : '#fffbff',
        "inverse-primary" : '#d0bcff',
        "secondary" : '#944a00',
        "on-secondary" : '#ffffff',
        "secondary-container" : '#fd933d',
        "on-secondary-container" : '#693300',
        "tertiary" : '#855000',
        "on-tertiary" : '#ffffff',
        "tertiary-container" : '#a76500',
        "on-tertiary-container" : '#fffbff',
        "error" : '#ba1a1a',
        "on-error" : '#ffffff',
        "error-container" : '#ffdad6',
        "on-error-container" : '#93000a',
        "primary-fixed" : '#e9ddff',
        "primary-fixed-dim" : '#d0bcff',
        "on-primary-fixed" : '#23005c',
        "on-primary-fixed-variant" : '#5516be',
        "secondary-fixed" : '#ffdcc5',
        "secondary-fixed-dim" : '#ffb783',
        "on-secondary-fixed" : '#301400',
        "on-secondary-fixed-variant" : '#713700',
        "tertiary-fixed" : '#ffdcbb',
        "tertiary-fixed-dim" : '#ffb869',
        "on-tertiary-fixed" : '#2c1700',
        "on-tertiary-fixed-variant" : '#673d00',
        "background" : '#f7f9fb',
        "on-background" : '#191c1e',
        "surface-variant" : '#e0e3e5'
      },
      borderRadius: {
        DEFAULT: "1rem",
        lg: "2rem",
        xl: "3rem",
        full: "9999px",
      },
      spacing: {
        "section-padding-mobile": "1.5rem", /* 24px */
        "section-padding-desktop": "4rem", /* 64px */
        "container-max-width": "80rem", /* 1280px */
        "base": "0.5rem", /* 8px */
        "gutter": "1.5rem", /* 24px */
      },
      fontFamily: {
        'display-lg': ['Plus Jakarta Sans', 'sans-serif'],
        'headline-lg': ['Plus Jakarta Sans', 'sans-serif'],
        'headline-lg-mobile': ['Plus Jakarta Sans', 'sans-serif'],
        'title-md': ['Plus Jakarta Sans', 'sans-serif'],
        'body-lg': ['Plus Jakarta Sans', 'sans-serif'],
        'body-md': ['Plus Jakarta Sans', 'sans-serif'],
        'label-sm': ['Plus Jakarta Sans', 'sans-serif'],
      },
      fontSize: {
        'display-lg': [
          '3rem', /* 48px */
          {
            lineHeight: '1.1',
            letterSpacing: '-0.02em',
            fontWeight: '700',
          },
        ],
        'headline-lg': [
          '2rem', /* 32px */
          {
            lineHeight: '1.2',
            letterSpacing: '-0.01em',
            fontWeight: '600',
          },
        ],
        'headline-lg-mobile': [
          '1.75rem', /* 28px */
          {
            lineHeight: '1.2',
            fontWeight: '600',
          },
        ],
        'title-md': [
          '1.25rem', /* 20px */
          {
            lineHeight: '1.4',
            fontWeight: '600',
          },
        ],
        'body-lg': [
          '1.125rem', /* 18px */
          {
            lineHeight: '1.6',
            fontWeight: '400',
          },
        ],
        'body-md': [
          '1rem', /* 16px */
          {
            lineHeight: '1.6',
            fontWeight: '400',
          },
        ],
        'label-sm': [
          '0.8125rem', /* 13px */
          {
            lineHeight: '1.2',
            letterSpacing: '0.05em',
            fontWeight: '600',
          },
        ],
      },
      borderRadius: {
        sm: '0.5rem',
        DEFAULT: '1rem',
        md: '1.5rem',
        lg: '2rem',
        xl: '3rem',
        full: '9999px',
      },
      spacing: {
        base: '0.5rem', /* 8px */
        'section-padding-desktop': '4rem', /* 64px */
        'section-padding-mobile': '1.5rem', /* 24px */
        gutter: '1.5rem', /* 24px */
        'container-max-width': '80rem', /* 1280px */
      },

    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('flowbite/plugin'),
  ],
}
