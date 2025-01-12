DEFAULT:= Serve
Yarn:
	yarn install
.PHONY: yarn

Composer:
	composer install
.PHONY: composer

Key:
	php artisan key:generate
.PHONY: key

Migrate:
	php artisan migrate
.PHONY: migrate

Seed: Migrate
	php artisan db:seed DatabaseSeeder
.PHONY: seed

Serve:
	cmd /c "start /B php artisan queue:work"
	cmd /c "start /B php artisan schedule:work"
	php artisan serve --host=192.168.0.193 --port=8000
.PHONY: serve
