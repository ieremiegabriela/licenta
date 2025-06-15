document.addEventListener("DOMContentLoaded", (event) => {
	document.querySelector("a#home").classList.add("active");
	document.addEventListener("beforeUnload", (event) => {
		document.querySelector("a#home").classList.remove("active");
	});
});
