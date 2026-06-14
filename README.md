# laravel-docker-template
## 環境構築
### Dockerビルド
・git clone git@github.com:Estra-Coachtech/laravel-docker-template.git  
・mv laravel-docker-template flea-market-app  
・cd flea-market-app  
・git remote set-url origin git@github.com:fujiurin/flea-market-app.git  
・git remote -v  
・git add .  
・git commit -m "リモートリポジトリの変更"  
・git push origin main  
・docker-compose up -d --build  

### Laravel環境構築
・docker-compose exec php bash  
・composer install  
・cp .env.example .env  
・.envを以下に変更
```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```
### アプリケーションキーの作成
・php artisan key:generate

### マイグレーションの実行
・php artisan migrate

### シーディングの実行
・php artisan db:seed

## 使用技術
・PHP 8.4.13  
・MySQL 8.0.26
・Laravel 8.83.29 
・Mailhog（メール確認）  

## ER図 
![ER図](docs/time-tracker-ER図.drawio.png)
