const ImaginatorView = {

	init() {
		this.el = document.querySelector(".imaginator-view");

		if (!this.el) {
			return false;
		}

		this.vue = new Vue(this.vueOptions());

		this.isInited = true;
		return true;
	},

	vueOptions() {
		return {
			el: ".imaginator-view",

			methods: {
				exitLightbox(event) {
					event.preventDefault();
					if(typeof window.parent.swal === 'undefined') {
						return;
					}

					window.parent.lightboxResult = $(event.target).data('imaginator-id');
					//click cancel  because .close() or .closeModal() don't call the onClose function
					window.parent.swal.clickCancel();
				}
			}
		};
	}
};