module.exports = {
  syntax: 'postcss-scss',
  plugins: {
    'autoprefixer': {},
    'postcss-combine-duplicated-selectors': {},
    'postcss-sort-media-queries': {
      sort: 'mobile-first'
    }
  },
};
