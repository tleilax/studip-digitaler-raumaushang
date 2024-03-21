const webpack = require('webpack');
const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const MomentLocalesPlugin = require('moment-locales-webpack-plugin');
const MomentTimezoneDataPlugin = require('moment-timezone-data-webpack-plugin');

module.exports = {
    entry: {
        common: ['./resources/entries.js'],
        'current-view': ['./resources/entries-current-view.js'],
        'room-view': ['./resources/entries-room-view.js'],
    },
    output: {
        chunkFilename: '[id]-[chunkhash].chunk.js',
        filename: '[name].js',
        publicPath: undefined,
        path: path.resolve(__dirname, 'assets')
    },
    devtool: 'source-map',
    module: {
        rules: [
            {
                test: /\.scss$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    {
                        loader: 'css-loader',
                        options: {
                            url: false,
                            importLoaders: 2
                        }
                    },
                    'postcss-loader',
                    {
                        loader: 'sass-loader',
                        options: {
                            implementation: require('sass'),
                            additionalData: "@import '_scss-prefix.scss';",
                        }
                    },
                ],
            },
            {
                test: /\.js$/,
                use: {
                    loader: 'babel-loader'
                }
            },
        ],
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: '[name].css',
            chunkFilename: '[name]-[contenthash].css'
        }),
        new MomentLocalesPlugin({
            localesToKeep: ['en', 'de']
        }),
        new MomentTimezoneDataPlugin({
            startYear: 2022,
            endYear: 2030,
            matchCountries: 'DE',
            matchZones: 'Europe/Berlin'
        }),
        new webpack.ProvidePlugin({
            $: "jquery",
            jQuery: "jquery",
        }),
    ],
    resolve: {
        extensions: ['.js'],
        alias: {
            '@': path.resolve(__dirname, 'resources'),
        },
    }
};
