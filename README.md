### Instalaciones

- composer install
- php artisan key:generate

### Migraciones
- php artisan migrate --path=/database/migrations/2024_07_12_182236_create_usuarios_v3_table.php
- php artisan migrate --path=/database/migrations/2024_07_12_184927_create_password_resets_v3_table.php
- php artisan migrate --path=/database/migrations/2024_07_12_185753_create_failed_jobs_v3_table.php
- php artisan migrate --path=/database/migrations/2024_07_12_190409_create_roles_v3_table.php
- php artisan migrate --path=/database/migrations/2024_07_12_191600_create_permisos_v3_table.php
- php artisan migrate --path=/database/migrations/2024_07_12_192405_create_roles_usuarios_v3_table.php
- php artisan migrate --path=/database/migrations/2024_07_12_193627_create_permisos_roles_v3_table.php
- php artisan migrate --path=/database/migrations/2024_07_12_202028_create_odt_v3_table.php
- php artisan migrate --path=/database/migrations/2024_07_12_204148_create_bitacoras_v3_table.php

### Seeders
- php artisan db:seed --class=PermisoSeeder
- php artisan db:seed --class=RolSeeder
- php artisan db:seed --class=PermisosRolesSeeder
- php artisan db:seed --class=permisosRolesYUsuarios
- php artisan db:seed --class=usuarios_
- php artisan db:seed --class=RolesUsuariosSeeder
- php artisan db:seed --class=PlataformaGpsSeeder
- php artisan db:seed --class=PlataformaTmsSeeder
- php artisan db:seed --class=ClientesTmsSeeder

### Bitacora

- Nos Basamos en el backend utilizado en V3 y limpiamos el proyecto para dejarlo blanco 
- instalamos las librerias composer install
- Genereamos la Key necesaria para laravel y las sessiones que se mantendra
- Creamos las migraciones para las tablas de inicio de session
- Creamos los seeders basicos para la insersion de datos sobre roles, permisos hacia el usuario


