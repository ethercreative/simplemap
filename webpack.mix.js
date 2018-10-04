const mix = require("laravel-mix");

mix.js("resources/js/Field.js", "src/web/assets/dist/")
	.sourceMaps()
	.disableNotifications();