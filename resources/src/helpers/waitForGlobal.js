export default function waitForGlobal (property, callback) {
	if (window.hasOwnProperty(property)) {
		callback();
		return;
	}

	const i = setInterval(() => {
		if (window.hasOwnProperty(property)) {
			callback();
			clearTimeout(i);
		}
	});
}
