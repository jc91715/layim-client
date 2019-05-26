## 客户端源码
[服务端源码](https://github.com/jc91715/layim-server)

## 使用方法

使用之前请到 [LayIm官网获取授权](http://layim.layui.com/)
把 dist/lay/modules/layim.js 放至项目根目录public/layim/dist/lay/modules/ 下即可
### 1 clone项目 源代码

```
    git clone https://github.com/jc91715/layim-client.git project
``` 
### 2 安装依赖

```
cd project

composer install
```

### 3 配置数据库和wbsocket_ip地址,并迁移

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=xxx
DB_USERNAME=xxx
DB_PASSWORD=xxx
```

```
WBSOCKET_IP=your layim-server ip
```
```
php artisan migrate
```
### 4 访问你的域名即可

yourdomain.com

## 加群交流

![加群交流](./public/c5039a7fdea6ef7f9b3d921e2d5e552.jpg)

开源不易，请喝个咖啡

微信赞赏

![微信赞赏](./public/17111835c8e51c101dd4ba9cb8cb578.png)

支付宝赞赏

![支付宝赞赏](./public/b0efde858d50b30c34d23ac5d01e35f.png)
