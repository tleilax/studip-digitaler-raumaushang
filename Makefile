NPM_BIN = $(shell npm bin)

build: npm
	npm run build

watch: npm
	watch -n 10 npm run build

npm: node_modules/.package-lock.json

node_modules/.package-lock.json: package.json package-lock.json
	npm install
