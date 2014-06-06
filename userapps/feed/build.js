({
    name: "main",
    out: "js/main.build.js",

	paths: {
		'jquery' : '/var/www/uwap/engine/core/js/jquery-1.8.3',
		'uwap-core' : '/var/www/uwap/engine/core',
		'uwap-core/bootstrap/js/bootstrap': '/var/www/uwap/engine/core/bootstrap/js/bootstrap.min',
		'uwap': 'https://feed.uwap.org/_/js',
		'requireLib': '/var/www/uwap/engine/js/require'
	},
	// include: 'requireLib',
	baseUrl: "js",
	shim: {
		'uwap-core/js/jquery.tmpl': {deps: ['jquery'], exports: 'jQuery'},
		'uwap-core/bootstrap/js/bootstrap': {
			deps: ['jquery', 'uwap-core/bootstrap/js/bootstrap-loadcss']
		},
		'uwap-core/bootstrap/js/bootstrap-modal': {deps: ['jquery', 'uwap-core/bootstrap/js/bootstrap'], exports: 'jQuery'},
		'uwap-core/bootstrap/js/bootstrap-button': {deps: ['jquery', 'uwap-core/bootstrap/js/bootstrap'], exports: 'jQuery'},
		'uwap-core/bootstrap/js/bootstrap-tooltip': {deps: ['jquery', 'uwap-core/bootstrap/js/bootstrap'], exports: 'jQuery'},
		'uwap-core/bootstrap/js/bootstrap-collapse': {deps: ['jquery', 'uwap-core/bootstrap/js/bootstrap'], exports: 'jQuery'},
		'uwap-core/bootstrap/js/bootstrap-dropdown': {deps: ['jquery', 'uwap-core/bootstrap/js/bootstrap'], exports: 'jQuery'},
		'uwap-core/bootstrap/js/bootstrap-transition': {deps: ['jquery', 'uwap-core/bootstrap/js/bootstrap'], exports: 'jQuery'},
		'uwap-core/bootstrap/js/bootstrap-alert': {deps: ['jquery', 'uwap-core/bootstrap/js/bootstrap'], exports: 'jQuery'},
		'uwap-core/bootstrap/js/bootstrap-scrollspy': {deps: ['jquery', 'uwap-core/bootstrap/js/bootstrap'], exports: 'jQuery'},
		'uwap-core/bootstrap/js/bootstrap-tab': {deps: ['jquery', 'uwap-core/bootstrap/js/bootstrap'], exports: 'jQuery'},
		'uwap-core/bootstrap/js/bootstrap-popover': {deps: ['jquery', 'uwap-core/bootstrap/js/bootstrap'], exports: 'jQuery'},
		'uwap-core/bootstrap/js/bootstrap-carousel': {deps: ['jquery', 'uwap-core/bootstrap/js/bootstrap'], exports: 'jQuery'},
		'uwap-core/bootstrap/js/bootstrap-typeahead': {deps: ['jquery', 'uwap-core/bootstrap/js/bootstrap'], exports: 'jQuery'}
	}
})
