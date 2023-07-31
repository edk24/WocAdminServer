up:
	docker compose up -d

build:
	docker compose build

down:
	docker compose down

install:
	docker run --rm --interactive --tty -v .:/app composer:2.4 install