export default function error (e) {
	window.Craft.cp.displayError('<strong>SimpleMap:</strong> ' + e.message);

	console.error.apply(console, [
		'%cSimpleMap: %c',
		'font-weight:bold;',
		'font-weight:normal;',
		e,
	]);
}