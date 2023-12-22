NPM_BIN = $(shell npm bin)

build: clean npm
	npm run build

build-dev: clean npm
	npm run build-dev

watch: clean npm
	npm rum watch

watch-dev: clean npm
	npm rum watch-dev

npm: node_modules/.package-lock.json

node_modules/.package-lock.json: package.json package-lock.json
	npm install

clean:
	-rm assets/*.{css,js}
