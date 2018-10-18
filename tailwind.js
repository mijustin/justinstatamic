/*
Tailwind - The Utility-First CSS Framework
*/

let defaultConfig = require('tailwindcss/defaultConfig')()


/*
|-------------------------------------------------------------------------------
| Colors                                    https://tailwindcss.com/docs/colors
|-------------------------------------------------------------------------------
|
*/

let colors = {
  'transparent':    'transparent',
  'black':          '#1a1b1c',
  'grey':           '#73808C',
  'grey-dark':      '#3C3C3C',
  'grey-light':     'rgba(237, 237, 237, .27)',
  'white':          '#ffffff',
  'blue':           '#0000ff',
  'blue-dark':      '#144A8D',
  'yellow':         '#ffff00',
  'yellow-light':   '#fff5c4',
}

module.exports = {

  /*
  |-----------------------------------------------------------------------------
  | Colors                                  https://tailwindcss.com/docs/colors
  |-----------------------------------------------------------------------------
  |
  | .error { color: config('colors.red') }
  |
  */

  colors: colors,


  /*
  |-----------------------------------------------------------------------------
  | Screens                      https://tailwindcss.com/docs/responsive-design
  |-----------------------------------------------------------------------------
  |
  | Class name: .{screen}:{utility}
  |
  */

  screens: {
    'md': '768px',
    'lg': '992px',
    'xl': '1200px'
  },


  /*
  |-----------------------------------------------------------------------------
  | Fonts                                    https://tailwindcss.com/docs/fonts
  |-----------------------------------------------------------------------------
  |
  | Class name: .font-{name}
  |
  */

  fonts: {
    'mono': [
        'Roboto Mono',
        'monospace'
    ],
    'hand': [
        'Shadows Into Light',
        'cursive'
    ]
  },


  /*
  |-----------------------------------------------------------------------------
  | Text sizes                         https://tailwindcss.com/docs/text-sizing
  |-----------------------------------------------------------------------------
  | text-size
  |
  */

  textSizes: {
    'sm':   '12px',
    'md':   '14px',
    'base': '16px',
    'lg':   '24px',
    'xl':   '32px',
    '2xl':  '48px',
  },


  /*
  |-----------------------------------------------------------------------------
  | Font weights                       https://tailwindcss.com/docs/font-weight
  |-----------------------------------------------------------------------------
  |
  | Class name: .font-{weight}
  |
  */

  fontWeights: {
    'normal': 400,
    'bold': 700
  },


  /*
  |-----------------------------------------------------------------------------
  | Leading (line height)              https://tailwindcss.com/docs/line-height
  |-----------------------------------------------------------------------------
  |
  | Class name: .leading-{size}
  |
  */

  leading: {
    'none': 1,
    'tight': 1.25,
    'normal': 1.5,
    'loose': 2,
  },


  /*
  |-----------------------------------------------------------------------------
  | Tracking (letter spacing)       https://tailwindcss.com/docs/letter-spacing
  |-----------------------------------------------------------------------------
  |
  | Class name: .tracking-{size}
  |
  */

  tracking: {
    'normal': '0',
    'wide': '0.15em',
  },


  /*
  |-----------------------------------------------------------------------------
  | Text colors                         https://tailwindcss.com/docs/text-color
  |-----------------------------------------------------------------------------
  |
  | Class name: .text-{color}
  |
  */

  textColors: colors,


  /*
  |-----------------------------------------------------------------------------
  | Background colors             https://tailwindcss.com/docs/background-color
  |-----------------------------------------------------------------------------
  |
  | Class name: .bg-{color}
  |
  */

  backgroundColors: colors,


  /*
  |-----------------------------------------------------------------------------
  | Border widths                     https://tailwindcss.com/docs/border-width
  |-----------------------------------------------------------------------------
  |
  | Class name: .border{-side?}{-width?}
  |
  */

  borderWidths: {
    default: '1px',
    '0': '0',
    '2': '2px',
    '3': '3px',
    '4': '4px',
    '8': '8px',
  },


  /*
  |-----------------------------------------------------------------------------
  | Border colors                     https://tailwindcss.com/docs/border-color
  |-----------------------------------------------------------------------------
  |
  | Class name: .border-{color}
  |
  */

  borderColors: Object.assign({ default: colors['black'] }, colors),


  /*
  |-----------------------------------------------------------------------------
  | Border radius                    https://tailwindcss.com/docs/border-radius
  |-----------------------------------------------------------------------------
  |
  | Class name: .rounded{-side?}{-size?}
  |
  */

  borderRadius: {
    'none': '0',
    default: '8px',
    'lg': '16px',
    'full': '9999px',
  },


  /*
  |-----------------------------------------------------------------------------
  | Width                                    https://tailwindcss.com/docs/width
  |-----------------------------------------------------------------------------
  |
  | Class name: .w-{size}
  |
  */

  width: {
    'auto': 'auto',
    'px': '1px',
    '4': '1rem',
    '6': '1.5rem',
    '300': '300px',
    '1/2': '50%',
    '1/3': '33.33333%',
    '2/3': '66.66667%',
    '1/4': '25%',
    '3/4': '75%',
    'full': '100%',
  },


  /*
  |-----------------------------------------------------------------------------
  | Height                                  https://tailwindcss.com/docs/height
  |-----------------------------------------------------------------------------
  |
  | Class name: .h-{size}
  |
  */

  height: {
    'auto': 'auto',
    'px': '1px',
    '1': '0.25rem',
    '2': '0.5rem',
    '3': '0.75rem',
    '4': '1rem',
    '6': '1.5rem',
    '8': '2rem',
    '10': '2.5rem',
    '12': '3rem',
    '14': '3.5rem',
    '16': '4rem',
    '20': '5rem',
    '24': '6rem',
    '32': '8rem',
    '40': '10rem',
    '48': '12rem',
    '64': '16rem',
    '96': '24rem',
    '128': '32rem',
    '160': '40rem',
    '192': '48rem',
    'full': '100%',
    'screen': '100vh',
    'screen-1/2': '50vh'
  },


  /*
  |-----------------------------------------------------------------------------
  | Minimum width                        https://tailwindcss.com/docs/min-width
  |-----------------------------------------------------------------------------
  |
  | Class name: .min-w-{size}
  |
  */

  minWidth: {
    '0': '0',
    '320': '320px',
    'full': '100%',
  },


  /*
  |-----------------------------------------------------------------------------
  | Minimum height                      https://tailwindcss.com/docs/min-height
  |-----------------------------------------------------------------------------
  |
  | Class name: .min-h-{size}
  |
  */

  minHeight: {
    '0': '0',
    'full': '100%',
    'screen': '100vh',
    'site': 'calc(100vh - 40px)'
  },


  /*
  |-----------------------------------------------------------------------------
  | Maximum width                        https://tailwindcss.com/docs/max-width
  |-----------------------------------------------------------------------------
  |
  | Class name: .max-w-{size}
  |
  */

  maxWidth: {
    'xs': '20rem',  // 320px
    'sm': '30rem',  // 480px
    'md': '40rem',  // 640px
    'lg': '50rem',  // 800px
    'xl': '60rem',  // 960px
    '2xl': '70rem', // 1120px
    '3xl': '80rem', // 1280px
    '4xl': '90rem', // 1440px
    '5xl': '100rem', // 1440px
    'half': '50%',
    'full': '100%'
  },


  /*
  |-----------------------------------------------------------------------------
  | Maximum height                      https://tailwindcss.com/docs/max-height
  |-----------------------------------------------------------------------------
  |
  | Class name: .max-h-{size}
  |
  */

  maxHeight: {
    'full': '100%',
    'sm': '10rem',
    'md': '20rem',
    'screen': '100vh',
    'screen-1/2': '50vh',
  },


  /*
  |-----------------------------------------------------------------------------
  | Padding                                https://tailwindcss.com/docs/padding
  |-----------------------------------------------------------------------------
  |
  | Class name: .p{side?}-{size}
  |
  */

  padding: {
    'px': '1px',
    '0': '0',
    'sm': '4px',
    '1': '8px',
    '2': '16px',
    '3': '24px',
    '4': '32px',
    '5': '40px',
    '6': '64px',
    '7': '80px',
    '8': '120px'
  },


  /*
  |-----------------------------------------------------------------------------
  | Margin                                  https://tailwindcss.com/docs/margin
  |-----------------------------------------------------------------------------
  |
  | Class name: .m{side?}-{size}
  |
  */

  margin: {
    'auto': 'auto',
    'px': '1px',
    '0': '0',
    'sm': '4px',
    '1': '8px',
    '2': '16px',
    '3': '24px',
    '4': '32px',
    '5': '40px',
    '6': '64px',
    '7': '80px',
    '8': '128px'
  },


  /*
  |-----------------------------------------------------------------------------
  | Negative margin                https://tailwindcss.com/docs/negative-margin
  |-----------------------------------------------------------------------------
  |
  | Class name: .-m{side?}-{size}
  |
  */

  negativeMargin: {
    'px': '1px',
    '0': '0',
    '1': '8px',
    '2': '16px',
    '3': '24px',
    '4': '32px',
    '5': '40px',
    '6': '64px',
    '7': '80px',
    '8': '128px',
  },


  /*
  |-----------------------------------------------------------------------------
  | Shadows                                https://tailwindcss.com/docs/shadows
  |-----------------------------------------------------------------------------
  |
  | Class name: .shadow-{size?}
  |
  */

  shadows: {
    default: '0 16px 20px -8px rgba(0,0,0,.15)',
    'fax': '18px -8px 0px rgba(0, 0, 0, .8)',
    'lg': '0 12px 20px 0px rgba(0,0,0,.25)',
    'blue': '0 0 0 4px ' + colors.blue,
    'none':  'none',
  },


  /*
  |-----------------------------------------------------------------------------
  | Z-index                                https://tailwindcss.com/docs/z-index
  |-----------------------------------------------------------------------------
  |
  | Class name: .z-{index}
  |
  */

  zIndex: {
    'auto': 'auto',
    '-1': -1,
    '0': 0,
    '10': 10,
  },


  /*
  |-----------------------------------------------------------------------------
  | Opacity                                https://tailwindcss.com/docs/opacity
  |-----------------------------------------------------------------------------
  |
  | Class name: .opacity-{name}
  |
  */

  opacity: {
    '0': '0',
    '25': '.25',
    '50': '.5',
    '75': '.75',
    '100': '1',
  },


  /*
  |-----------------------------------------------------------------------------
  | SVG fill                                   https://tailwindcss.com/docs/svg
  |-----------------------------------------------------------------------------
  |
  | Class name: .fill-{name}
  |
  */

  svgFill: {
    'current': 'currentColor',
  },


  /*
  |-----------------------------------------------------------------------------
  | SVG stroke                                 https://tailwindcss.com/docs/svg
  |-----------------------------------------------------------------------------
  |
  | Class name: .stroke-{name}
  |
  */

  svgStroke: {
    'current': 'currentColor',
  },


  /*
  |-----------------------------------------------------------------------------
  | Modules                  https://tailwindcss.com/docs/configuration#modules
  |-----------------------------------------------------------------------------
  |
  | Here is where you control which modules are generated and what variants are
  | generated for each of those modules.
  |
  | Currently supported variants: 'responsive', 'hover', 'focus'
  |
  | To disable a module completely, use `false` instead of an array.
  |
  */

  modules: {
    appearance: ['responsive'],
    backgroundAttachment: ['responsive'],
    backgroundColors: ['responsive', 'hover', 'group-hover'],
    backgroundPosition: ['responsive'],
    backgroundRepeat: ['responsive'],
    backgroundSize: ['responsive'],
    borderColors: ['responsive', 'hover'],
    borderRadius: ['responsive'],
    borderStyle: ['responsive'],
    borderWidths: ['responsive'],
    cursor: ['responsive'],
    display: ['responsive', 'group-hover'],
    flexbox: ['responsive'],
    float: ['responsive'],
    fonts: ['responsive'],
    fontWeights: ['responsive', 'hover'],
    height: ['responsive'],
    leading: ['responsive'],
    lists: ['responsive'],
    margin: ['responsive'],
    maxHeight: ['responsive'],
    maxWidth: ['responsive'],
    minHeight: ['responsive'],
    minWidth: ['responsive'],
    negativeMargin: ['responsive'],
    opacity: ['responsive', 'group-hover'],
    overflow: ['responsive'],
    padding: ['responsive'],
    pointerEvents: ['responsive'],
    position: ['responsive'],
    resize: ['responsive'],
    shadows: ['responsive', 'hover'],
    svgFill: [],
    svgStroke: [],
    textAlign: ['responsive'],
    textColors: ['responsive', 'hover', 'group-hover'],
    textSizes: ['responsive'],
    textStyle: ['responsive', 'hover'],
    tracking: ['responsive'],
    userSelect: ['responsive'],
    verticalAlign: ['responsive'],
    visibility: ['responsive'],
    whitespace: ['responsive'],
    width: ['responsive'],
    zIndex: ['responsive'],
  },


  /*
  |-----------------------------------------------------------------------------
  | Advanced Options         https://tailwindcss.com/docs/configuration#options
  |-----------------------------------------------------------------------------
  |
  | Here is where you can tweak advanced configuration options. We recommend
  | leaving these options alone unless you absolutely need to change them.
  |
  */

  options: {
    prefix: '',
    important: true,
    separator: ':',
  },

  plugins: [
    require('tailwindcss/plugins/container')({
      center: true,
      padding: '2rem',
    })
  ],

}
