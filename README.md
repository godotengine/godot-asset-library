# Godot's Asset Library

___

**Note:** This asset library backend and frontend is now in maintenance mode.
Feel free to submit bug fixes and small improvements, but please refrain from
working on large features. In the future, the [Godot Foundation](https://godot.foundation/)'s asset store
will deprecate this library.

___

REST API and frontend for Godot Engine's [official asset library](https://godotengine.org/asset-library).

[Endpoints](./API.md)

## Installation

Run the following commands to get a running installation of the project:

````bash
composer install
cp src/settings-local-example.php src/settings-local.php
````

Now you should proceed to update `src/settings-local.php` with your DB password and session secret.

## Container

It is also possible to run the asset library inside a container (e.g. with Docker).

First create a new settings file for the website.

```bash
cp src/settings-local-example.php src/settings-local.php
```

Update `src/settings-local.php` with your DB password and session secret.

Now continue from the `container` directory and create your own `docker-compose.yml` file.

```bash
cd container
cp docker-compose.example.yml docker-compose.yml
```

Adjust user and password inside the created `docker-compose.yml`.

Now build the container image

```bash
docker compose build
```

To run the whole composition

```bash
docker compose up -d
```

The website is now available at http://localhost:8080/asset-library.

## Browser support

When working on new features, keep in mind this website only supports
*evergreen browsers*:

- Chrome (latest version and N-1 version)
- Edge (latest version and N-1 version)
- Firefox (latest version, N-1 version, and latest ESR version)
- Opera (latest version and N-1 version)
- Safari (latest version and N-1 version)

Internet Explorer isn't supported.
