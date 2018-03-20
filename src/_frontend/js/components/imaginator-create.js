const ImaginatorCreate = {

	init() {
		this.el = document.querySelector(".imaginator-create");
		this.option = $(".imaginator-create__option");

		this.bindClickEvents.delete($('[data-delete-item]'));
		$('.tooltip').popup();
		$('.ui.dropdown').dropdown();

		if (!this.el) {
			return false;
		}

		this.vue = new Vue(this.vueOptions());

		this.isInited = true;
		return true;
	},

	vueOptions() {
		return {
			el: ".imaginator-create",

			data: {
				template: this.templateFromPHP,
				variations: this.variationsFromPHP,
				imaginator: this.imaginatorFromPHP,
				imaginatorSources: this.sourcesFromPHP,
				numberOfShownThumbnails: 0,
				isLoading: false,
				isSelected: false,
				appUrl: IMAGINATOR.appUrl,
				firstLoaded: false,
				isSaving: false,
			},

			mounted() {
				this.$nextTick(() => {
					this.$dropzone = $(".imaginator-create__dropzone");
					this.setDropzone();
					this.imaginator.imaginator_template_id = this.template.id;
					if (this.imaginatorSources.length < 1) {
						this.imaginatorSources = [];
					}
					this.onLoadHandler();
					this.waitForShowThumbnailsOnLoad();
				});

				_.each(this.variations, (variation, variationKey) => {
					if (!this.hasSource(variation)) {
						this.$set(variation, 'isSelected', true);
					}
					this.$set(variation, 'isResizing', false);
				});
			},

			methods: {
				selectVariation(variation, event) {
					if (event) {
						if (variation.isResizing) {
							// pokud zrovna resizuju, nebude to hazet alert kvuli click eventu
							if (event && event.target.classList.contains('cr-overlay') || event.target.classList.contains('cr-slider')) {
								return false;
							}
							else {
								alert('Dokončete ořezávání');
								return false;
							}
						}

						this.$nextTick(() => {
							if (!this.hasSomeWithoutSources && !this.hasAllWithoutSources) {
								let v = _.find(this.variations, {id: variation.id});
								this.$set(v, 'isSelected', !v.isSelected);
							}
						});
					}
					else {
						if (variation.isResizing) {
							alert('Dokončete ořezávání');
							return false;
						}

						let v = _.find(this.variations, {id: variation.id});
						this.$set(v, 'isSelected', !v.isSelected);
					}
				},

				selectAllVariants() {
					this.variations.forEach((variation) => {
						this.$set(variation, 'isSelected', true);
					});
				},

				selectNoneVariants() {
					this.variations.forEach((variation) => {
						if (variation.isResizing && this.hasAllSources) {
							return false;
						}
						else {
							this.$set(variation, 'isSelected', false);
						}
					});
				},

				selectVariationsWithoutSource() {
					let variationIds = _.map(this.variations, 'id');
					let imaginatorSourceIds = _.map(this.imaginatorSources, 'imaginator_variation_id');
					let idsArrayDifference = _.difference(variationIds, imaginatorSourceIds);

					$.each(idsArrayDifference, (index, id) => {
						this.selectVariation(_.find(this.variations, {id: parseInt(id)}));
						this.$set(_.find(this.variations, {id: parseInt(id)}), 'hasNoSource', true);
					});
				},

				onLoadHandler() {
					if (!this.firstLoaded) { // pri nacteni stranky
						if (this.hasAllSources) {
							this.selectAllVariants();
						}
						else if (this.hasSomeWithoutSources) {
							this.selectVariationsWithoutSource();
						}
						else if (this.hasAllWithoutSources) {
							if (this.isEditing) {
								this.selectAllVariants();
								this.variations.forEach((variation) => {
									this.$set(variation, 'hasNoSource', true);
								});
							}
						}
						this.firstLoaded = true;
					}
					else { // po nahrati obrazku
						if (!this.hasSomeWithoutSources) { // mame vsechny source?
							if (!this.isSomethingResized) { // neni nic resizovano?
								this.selectAllVariants();
							}

							this.variations.forEach((variation) => {
								this.$set(variation, 'hasNoSource', false);
							});
						}
					}
				},

				setDropzone() {
					let url = this.$dropzone.data('url');
					this.dz = this.$dropzone.dropzone({
						url: url,
						maxFiles: 1,
						acceptedFiles: "image/jpeg,image/png",
						sending: (file, xhr, formData) => {
							if (this.hasAllSources) {
								formData.append('variations', JSON.stringify(_.filter(this.variations, {isSelected: true})));
							} else {
								formData.append('variations', JSON.stringify(this.variations));
							}
							this.isLoading = true;

							if (this.numberOfShownThumbnails !== 0) {
								this.numberOfShownThumbnails = this.variations.length - this.numberOfSelected;
							}

						},
						success: (dzResponse, uploadResponse) => {
							this.setSource(uploadResponse);
							Dropzone.forElement(".imaginator-create__dropzone").destroy();
							this.setDropzone();
							this.$nextTick(() => {
								this.onLoadHandler();
								this.setAllSelectedCroppies();
								this.isLoading = false;
							});
							this.waitForShowThumbnailsOnLoad();
						}
					});

					Dropzone.forElement(".imaginator-create__dropzone").on("addedfile", () => {
						if (this.hasAllSources && !this.isSomethingSelected) {
							alert('Vyberte alespoň jednu možnost');
							return false;
						}
					});
				},

				waitForShowThumbnailsOnLoad() {
					_.forEach(this.variations, (variation) => {
						this.$nextTick(() => {
							let thumbnail = $('.image-to-crop--' + variation.id);
							thumbnail.unbind('load');
							thumbnail.on('load', () => {
								this.numberOfShownThumbnails++;
								this.$nextTick(() => {
									thumbnail.unbind('load');
								});
							});
						});
					});
				},

				waitForShowThumbnailOnResize(variation) {
					this.$nextTick(() => {
						let thumbnail = $('.image-to-crop--' + variation.id);
						thumbnail.unbind('load');
						this.$set(variation, 'isResizeLoading', true);
						thumbnail.on('load', () => {
							this.$nextTick(() => {
								this.$set(variation, 'isResizeLoading', false);
								thumbnail.unbind('load');
							});
						});
					});
				},

				sendFile() {
					if (window.sendFileRequest) {
						window.sendFileRequest.abort();
					}

					this.isSaving = true;
					/*
					*	TODO hotfix ukladania imaginatorov, cez request sa posielalo priliz moc dat ak sa osobitne posielali
					*	TODO imaginator sources a imaginator. Jednou bolo v imaginator objektu a potom este raz v sources, ako
					*	TODO docasny hotfix pred predhochodom na orez cez koordinace sa pridal tento hotfix.
					*/
					this.$set(this.imaginator, 'imaginator_sources', this.imaginatorSources);

					window.sendFileRequest = $.ajax({
						type: "POST",
						url: IMAGINATOR.storeUrl,
						dataType: 'json',
						data: {
							imaginator: this.imaginator,
						},
						success: (response) => {
							this.isSaving = false;
							this.imaginator = response.imaginator;
							this.imaginatorSources = response.imaginatorSources;

							if(typeof window.parent.swal === 'undefined') {
								return;
							}

							window.parent.lightboxResult = this.imaginator.id;
							//click cancel  because .close() or .closeModal() don't call the onClose function
							window.parent.swal.clickCancel();
						},
						error: (response) => {
							if (response.status === 0) {
								return false;
							}
							console.log(response);
						}
					});
				},

				hasSource(variation) {
					return (typeof variation.source !== 'undefined');
				},

				setSource(uploadResponse) {
					_.forEach(uploadResponse.imaginatorSources, (requestSource) => {
						let imaginatorSource = {};
						let sourceToChange = _.find(this.imaginatorSources, {imaginator_variation_id: requestSource.imaginator_variation_id});
						if (sourceToChange) {
							sourceToChange.source = requestSource.source;
						}
						else {
							imaginatorSource.imaginator_variation_id = requestSource.imaginator_variation_id;
							imaginatorSource.source = requestSource.source;
							this.imaginatorSources.push(imaginatorSource);
						}
					});
				},

				sourceToShow(variation) {
					let imaginatorSource = _.find(this.imaginatorSources, {imaginator_variation_id: variation.id});

					if (!imaginatorSource) {
						return false;
					}
					if (!imaginatorSource.resized) {
						return this.appUrl + '/' + imaginatorSource.source;
					}
					if (imaginatorSource.resized.indexOf('base64') !== -1) {
						return imaginatorSource.resized;
					}

					return this.appUrl + '/' + imaginatorSource.resized;
				},

				triggerOpenDropzone() {
					$('.imaginator-create__dropzone').trigger('click');
				},

				setAllCroppies() {
					_.forEach(this.variations, (variation) => {
						this.setCroppie(variation);
					});
				},

				setAllSelectedCroppies() {
					_.forEach(this.variations, (variation) => {
						if (variation.isSelected) {
							this.setCroppie(variation);
						}
					});
				},

				setCroppie(variation, event) {
					Dropzone.forElement(".imaginator-create__dropzone").removeEventListeners();
					this.$set(variation, 'isResizing', true);
					this.$set(variation, 'isResizeLoading', false);

					let thumbnailImage = $('.imaginator-create__thumbnail-item-image');
					let imageToCrop = $('.image-to-crop--' + variation.id);

					let sourceToChange = _.find(this.imaginatorSources, {imaginator_variation_id: variation.id});

					if (sourceToChange) {
						imageToCrop.attr('src', this.appUrl + '/' + sourceToChange.source);
						if (event) {
							this.$nextTick(() => {
								this.waitForShowThumbnailOnResize(variation);
							});
						}
					}

					let variationViewRatio = variation.width / variation.height;
					let imgWrapViewRatio = thumbnailImage.width() / thumbnailImage.height();
					let imgWrapWidth = thumbnailImage.width();
					let imgWrapHeight = thumbnailImage.height();
					let cropViewport = null;

					if (variation.width >= variation.height) {
						if (imgWrapViewRatio >= variationViewRatio) {
							cropViewport = {
								width: imgWrapHeight * variationViewRatio,
								height: imgWrapHeight
							};
						}
						else {
							cropViewport = {
								width: imgWrapWidth,
								height: imgWrapWidth / variationViewRatio
							};
						}
					}
					else {
						if (imgWrapViewRatio >= variationViewRatio) {
							cropViewport = {
								width: imgWrapHeight * variationViewRatio,
								height: imgWrapHeight
							};
						}
						else {
							cropViewport = {
								width: imgWrapWidth,
								height: imgWrapWidth / variationViewRatio
							};
						}
					}

					variation.crop = imageToCrop.croppie({
						viewport: cropViewport
					});
				},

				getCroppedImage(variation) {
					this.$set(variation, 'isResizeLoading', true);

					if (!variation.isResizeLoading) {
						return false;
					}

					let thisThumbnailImageWrap = $('.thumbnail-image--' + variation.id);
					let toCrop = variation.crop;

					this.$nextTick(() => {
						setTimeout(() => {
							toCrop
								.croppie('result', {
									type: 'base64',
									size: {
										width: variation.width,
										height: variation.height
									}
								})
								.then((base) => {
									// zapis base64 orezaneho image do imaginatorSources
									let imaginatorSource = _.find(this.imaginatorSources, {imaginator_variation_id: variation.id});
									this.$set(imaginatorSource, 'resized', base);

									// smazani croppie a vlozeni oriznuteho image do thumbnailu
									toCrop.croppie('destroy');
									thisThumbnailImageWrap.empty();

									setTimeout(() => {
										thisThumbnailImageWrap.append('<img src="' + imaginatorSource.resized + '" class="image-to-crop--' + variation.id + '" alt="">');
										setTimeout(() => {
											this.$set(variation, 'isResizeLoading', false);
										});
									});

									variation.isResizing = false;

									if (!this.isSomethingResizing) {
										Dropzone.forElement(".imaginator-create__dropzone").setupEventListeners();
									}
								});
						}, 50);
					});
				},

				getAllCroppedImages() {
					let resizingVariations = _.filter(this.variations, {isResizing: true});
					resizingVariations.forEach((variation) => {
						this.getCroppedImage(variation);
					});
				}
			},


			computed: {
				hasAllSources() {
					let map = _.map(this.variations, (variation) => {
						let imaginatorSource = _.find(this.imaginatorSources, {imaginator_variation_id: variation.id});

						if (!imaginatorSource) {
							return false;
						}

						return (imaginatorSource.source !== null && imaginatorSource.source.length > 0);
					});

					return (map.indexOf(false) === -1);
				},

				hasSomeWithoutSources() {
					return (this.variations.length > this.imaginatorSources.length && this.imaginatorSources.length !== 0);
				},
				hasAllWithoutSources() {
					return (this.imaginatorSources.length === 0);
				},

				hasAllSourcesOrImaginatorHasId() {
					return (this.hasAllSources || this.imaginator.id);
				},

				isSelectedMultiple() {
					return this.numberOfSelected > 1;
				},

				isSomethingSelected() {
					return this.numberOfSelected > 0;
				},

				numberOfSelected() {
					return _.filter(this.variations, (variation) => {
						return variation.isSelected;
					}).length;
				},

				numberOfResizing() {
					return _.filter(this.variations, (variation) => {
						return variation.isResizing;
					}).length;
				},

				numberOfResized() {
					return _.filter(this.imaginatorSources, (imaginatorSource) => {
						return imaginatorSource.resized;
					}).length;
				},

				isSomethingResized() {
					return (this.numberOfResized > 0);
				},

				isAllResized() {
					return (this.numberOfResized === this.variations.length && this.numberOfResized !== 0);
				},

				isSomethingResizing() {
					return this.numberOfResizing > 0;
				},

				isEditing() {
					return (typeof this.imaginator.created_at !== 'undefined' && this.imaginator.created_at !== null);
				},

				thumbnailDimensions() {
					let thumbnailHeight = null,
						thumbnailWidth = null;

					if (this.variations.length > 2) {
						if (this.variations.length % 2 !== 0) {
							thumbnailHeight = (100 / (this.variations.length + 1)) * 2;
						}
						else {
							thumbnailHeight = (100 / this.variations.length) * 2;
						}
						thumbnailWidth = '50%';
					}
					else {
						thumbnailHeight = (100 / this.variations.length);
						thumbnailWidth = '100%';
					}
					return {
						'height': thumbnailHeight + '%',
						'width': thumbnailWidth,
					};
				},

				allThumbnailsShown() {
					return this.numberOfShownThumbnails === this.variations.length;
				},
			}
		};
	},
	bindClickEvents: {
		delete: ($element) => {
			let $deleteName = $('#deleteName', '.modal'),
				$deleteConfirm = $('#deleteConfirm', '.modal');

			$element.on('click', function () {
				let $form = $(this).parents('form');

				$('#deleteModal').modal('show');

				$deleteName.html($form.data('delete-name'));

				$deleteConfirm.on('click', function () {
					$form.submit();
				});
			});
		}
	}
};