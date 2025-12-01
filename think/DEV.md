## 安装

~~~
composer update
~~~



## tp6-gii

### 介绍

1. 根据excel生成model和sql
2. 生成的model继承app\models下AR类。
3. 生成的类文件以 ` // ---------- Custom code below ----------` 为分界线，上面是tpgii自动生成代码，下面为自定义代码

### 安装

~~~
composer require shisou/tpgii
~~~

### 使用

1.  新建db.xlsx放入到think目录下，格式如下图：

* 命令自动生成`id,created_at,updated_at,status`，编写excel时请忽略

* 字段 `@表名 `   关联表名
* 字段 `@表名@ `   相互关联

   <img src="./doc/db.jpg" alt="20x20" style="zoom:25%;" />

2. 运行命令

    * 到 `think` 根目录运行命令: `php think tpgii`

    * 可跟参数  `model`  只生成model

    * 可跟参数 `sql`  只生成 `db.sql`

## tp6-init

### 介绍

- 生成基类控制器以及公共函数

### 安装
    composer require shisou/tpinit

## 去除右下角 trace 调试框
调试模式下是无法关闭 trace 调试的
移除 require-dev 中的扩展包需要添加 --dev 参数
~~~
composer remove topthink/think-trace --dev
~~~
如果需要再次使用 trace 调试，重新引入扩展即可
~~~
composer require topthink/think-trace --dev
~~~
    