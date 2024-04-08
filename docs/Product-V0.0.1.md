# product-v001版本


- ### 需执行的脚本
  - #### 数据库填充
  ```shell
  # 表迁移
  php vendor/bin/phinx seed:run
  
  # 数据迁移
  php vendor/bin/phinx migrate
  ```