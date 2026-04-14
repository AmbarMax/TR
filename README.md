<div align="center">
<img src="https://avatars1.githubusercontent.com/u/33844443" height="100px" alt="CorpSoft Logo">
    <h1 align="center">README Ambar</h1>
</div>

### Installing using Docker


> You need to have [docker](http://www.docker.com) (1.17.0+) and
[docker-compose](https://docs.docker.com/compose/install/) (1.14.0+) installed.

## You can install the application using the following commands:

### Firstly you need to clone the project and do some basic setup of the .env file:

```sh
cp .env.example .env
```

### You need to configure the env and change the variables in the values below, with ports in the range 49152 to 65535:
```sh
APP_SERVICE=web
APP_PORT=50500
FORWARD_DB_PORT=50501
FORWARD_REDIS_PORT=50502
FORWARD_MEILISEARCH_PORT=50503
FORWARD_MAILHOG_PORT=50504
FORWARD_MAILHOG_DASHBOARD_PORT=50505
VITE_PORT=50506
FORWARD_CENTRIFUGO_PORT=50507
```

> In `.env` file your need to set your HOST_UID=****.
> You can get your UID by the following command in the terminal: `id -u <username>`


### Second you need install Sail using the Composer package manager and after configuring a shell alias using the following command:
_If your existing local development environment allows you to install Composer dependencies_
```sh
composer require laravel/sail --dev
alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'
```

_Alias sail only available in the terminal where you entered them_
<br>
_Once the shell alias has been configured, you may execute Sail commands._
<br>
_You can add alias global to phpstorm terminal or other terminal_


### To start all of the Docker containers in the background, you may start Sail in "detached" mode:

```sh
sail up -d
```
_It may take some time to download the required images._

_To stop all of the containers run:_  `sail stop`

### When done, you need to execute the following commands:

```sh
sail composer install
sail artisan key:generate
sail artisan migrate
sail artisan db:seed
sail artisan storage:link
sail npm install
sail npm run dev
```

#### After this steps, you can access your app from [http://localhost:xxxxx](http://localhost:xxxxx).

### .env file
```sh

SFTP_HOST="v-buf-04.sparkedhost.us"
SFTP_USERNAME=
SFTP_PASSWORD=
SFTP_ROOT="/"

CENTRIFUGO_API_KEY=
CENTRIFUGO_SECRET=
CENTRIFUGO_URL=http://ambar-centrifugo:8020/api
CENTRIFUGO_WEBSOCKET=ws://localhost:8020/connection/websocket
CENTRIFUGO_CHANNEL_PREFIX="ambar-"
CENTRIFUGO_WEBSOCKET_CLIENT=ws://localhost:50507/connection/websocket

GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
GITHUB_REDIRECT_URI=http://localhost:50500/api/github/callback

DISCORD_CLIENT_ID=
DISCORD_CLIENT_SECRET=
DISCORD_REDIRECT_URI=http://localhost:50500/api/discord/callback
DISCORD_BOT_TOKEN=

STEAM_AUTH_API_KEYS=
STEAM_SECRET=
STEAM_REDIRECT_URI=http://localhost:50500/api/steam/callback
```
