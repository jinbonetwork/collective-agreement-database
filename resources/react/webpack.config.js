module.exports = {
  entry: './src/App.js',
  output: {
    path: 'public/js/',
    filename: 'bundle.js'
  },
  devServer: {
    inline: true,
    contentBase: 'public',
    publicPath: '/js/',
    historyApiFallback: true,
    host: 'dev.onebyte.kr',
    port: 1919
  },
  module: {
    loaders: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        loader: 'babel',
        query: {
          presets: ['react', 'es2015']
        }
      }
    ]
  }
};
