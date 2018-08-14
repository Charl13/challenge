# Challenge Pixelindustries

## Quickstart
### 1. Setup environment
To get up and running fast using docker execute the following commands:
```bash
docker-compose up
docker-compose exec php artisan migrate
```
### 2. Upload challenge.json
The json file can be uploaded to `/users/uploads` in a `POST` request.
```bash
curl localhost/users/upload -X POST -F 'users=@./challenge.json'
```
