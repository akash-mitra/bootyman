@servers(['web' => ['root@' . $ipaddress . ' -p' . $port . ' -i' . $key . ' -o StrictHostKeyChecking=no']])

@task('deploy')
    cd {{ $remotepath }}
    php artisan key:generate
    php artisan config:clear
    php artisan cache:clear
    php artisan event:clear
    php artisan route:clear

    {{-- User Add --}}
    sed -i "s|^APP_URL=.*$|APP_URL=http://{{ $ipaddress }}|" .env
    sed -i "s|^DOMAIN=.*$|DOMAIN={{ $ipaddress }}|" .env
    sed -i "s|^SESSION_DOMAIN=.*$|SESSION_DOMAIN={{ $ipaddress }}|" .env

    sed -i "s|^ADMIN_USER_NAME=.*$|ADMIN_USER_NAME=Administrator|" .env
    sed -i "s|^ADMIN_USER_EMAIL=.*$|ADMIN_USER_EMAIL={{ $email }}|" .env
    sed -i "s|^ADMIN_USER_PASSWORD=.*$|ADMIN_USER_PASSWORD={{ $password }}|" .env
    php artisan db:seed --class=UsersTableSeeder

@endtask
