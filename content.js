function parseAndReplaceComments () {
	let times = 0;
	let video;

	if (video = document.querySelector('video')) {
		video.remove();
	}

	[].slice.call(document.querySelectorAll('.comment-renderer-text-content'))
		.filter(function (commentNode) {
			times += 1;
			return /*times < 10 &&*/ !commentNode.getAttribute('data-yourude-parsed');
		})
		.forEach(function (commentNode) {
			let urlToFetch = 'https://yourude.com?text=' + encodeURIComponent(commentNode.innerText);
			commentNode.setAttribute('data-yourude-parsed', 1);
			fetch(urlToFetch)
				.then(function (res) {
					return res.json();
				})
				.then(function (responseJson) {
					if (!responseJson.isChanged) {
						return;
					}

					// TODO
					commentNode.style.color = 'green';
					commentNode.setAttribute('title', commentNode.innerText);
					commentNode.innerText = responseJson.text;
				})
				// .err(function (res) {
				//
				// })
			;
		})
	;
}

parseAndReplaceComments();
setInterval(parseAndReplaceComments, 1000);
