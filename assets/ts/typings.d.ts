interface StellarOptions {
    // Set scrolling to be in either one or both directions
  horizontalScrolling?: boolean;
  verticalScrolling?: boolean;

    // Set the global alignment offsets
  horizontalOffset?: number;
  verticalOffset?: number;

    // Refreshes parallax content on window load and resize
  responsive?: boolean;

    // Select which property is used to calculate scroll.
    // Choose 'scroll', 'position', 'margin' or 'transform',
    // or write your own 'scrollProperty' plugin.
  scrollProperty?: string;

    // Select which property is used to position elements.
    // Choose between 'position' or 'transform',
    // or write your own 'positionProperty' plugin.
  positionProperty?: string;

    // Enable or disable the two types of parallax
  parallaxBackgrounds?: boolean;
  parallaxElements?: boolean;

    // Hide parallax elements that move outside the viewport
  hideDistantElements?: boolean;

    // Customise how elements are shown and hidden
  hideElement?: ($el: JQuery) => void;
  showElement?: ($el: JQuery) => void;
}

interface JQuery {
  stellar(options?: StellarOptions): JQuery;
}

interface FastDOMTask<T> {
  fn: () => void;
  ctx?: T;
}

interface FastDOM {
  measure<T>(fn: () => void, ctx?: T): FastDOMTask<T>;
  mutate<T>(fn: () => void, ctx?: T): FastDOMTask<T>;
  clear(task: FastDOMTask<any>): boolean;
}

declare var fastdom: FastDOM;
declare module 'fastdom' {
  export = fastdom;
}
