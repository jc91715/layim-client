// const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

// mix.js('resources/js/app.js', 'public/js')
//    .sass('resources/sass/app.scss', 'public/css');

const mix = require('laravel-mix')
const path = require('path')
const HtmlWebpackPlugin = require('html-webpack-plugin');
const SpriteLoaderPlugin = require('svg-sprite-loader/plugin');
require('dotenv').config();

function resolve(dir) {
    return path.join(
        __dirname,
        '/resources/js/front',
        dir
    );
}
//加载svg
//https://github.com/JeffreyWay/laravel-mix/issues/1423#issuecomment-360731352
Mix.listen('configReady', webpackConfig => {
    // Add "svg" to image loader test
    const imageLoaderConfig = webpackConfig.module.rules.find(
        rule =>
            String(rule.test) ===
            String(/\.(woff2?|ttf|eot|svg|otf)$/)
    );
    imageLoaderConfig.exclude = resolve('icons');
});

mix.webpackConfig({
    plugins: [
        new HtmlWebpackPlugin({
            template: 'resources/js/front/index.html',
            filename: 'index.html',
            inject: true
        }),
        new SpriteLoaderPlugin()
    ],
    resolve: {
        extensions: ['.js', '.vue', '.json'],
        alias: {
            '@': __dirname + '/resources/js/front'
        },
    },
    module: {
        rules: [
            {
                test: /\.svg$/,
                loader: 'svg-sprite-loader',
                include: [resolve('icons')],
                options: {
                    symbolId: 'icon-[name]'
                }
            },
        ]
    }
});



mix.config.webpackConfig.output = {
    chunkFilename: 'js/[name].bundle.js',
    publicPath: '/build/front/'
};

mix.version()
mix.js('resources/js/front/bootstrap.js', 'js/bootstrap.js')
    .js('resources/js/front/app.js', 'js/front.js')
    .sass('resources/js/front/styles/app.scss', 'css/app.css') // 打包后台css
    .sass('resources/js/front/styles/index.scss', 'css/front.css') // 打包后台css
    .extract(['vue', 'vue-router', 'axios','mint-ui',]) // 提取依赖库
    .setResourceRoot('/build/front/') // 设置资源目录
    .setPublicPath('./public/build/front') // 设置 mix-manifest.json 目录
