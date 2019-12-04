class ImaginatorInput {
  constructor() {
    this.init();

    return this;
  }

  init() {
    let imaginatorInputs = document.querySelectorAll('[data-imaginator]');

    if (imaginatorInputs.length < 1) {
      return false;
    }

    for (let i = 0; i < imaginatorInputs.length; i++) {
      this.setImaginatorInput(imaginatorInputs[i]);
    }
  }

  clickHandler(event) {
    this.clickedInput = event.target;

    let elementValue = this.clickedInput.value,
      imaginatorTemplate = this.clickedInput.getAttribute('data-imaginator-template'),
      url = window.ImaginatorCreateUrl.replace('{template}', imaginatorTemplate);

    if (elementValue.length > 0) {
      url += '?imaginator=' + elementValue;
    }

    swal({
      html: '<iframe src="' + url + '" class="imaginator-lightbox"></iframe>',
      width: '90vw',
      showConfirmButton: false,
      showCloseButton: true,
      focusCancel: false,
      padding: '0px',
      animation: false,
      onClose: () => {
        if (typeof window.lightboxResult === 'undefined') {
          return;
        }

        setTimeout(() => {
          this.clickedInput.value = window.lightboxResult;
        });
      },
    }).catch(swal.noop);
  }

  setImaginatorInput(element) {
    element.removeEventListener('click', this.clickHandler);
    element.addEventListener('click', this.clickHandler);
  }

}

const imaginatorInput = new ImaginatorInput();