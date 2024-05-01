run-web:
	php artisan serve

run-migration-fresh:
	php artisan migrate:fresh --seed

init-docker-folders:
	mkdir -p ./docker_volumes/postgre