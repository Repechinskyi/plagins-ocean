<template>
    <textarea :name="modelName" :id="modelName" :value="value"></textarea>
</template>

<script>
  const CKEDITOR = window.CKEDITOR;

  export default {
    name: 'Ckeditor',
    props: {
      modelName: {
        required: true,
        type: String,
        default: 'ckeditor',
      },
      value: {
        required: true,
        type: String,
        default: '',
      },
      options: {
        required: false,
        type: Object,
        default() {
          return {
            height: '300px',
            language: 'ru-RU',
            toolbar: [
              'Format',
              ['Bold', 'Italic', 'Strike', 'Underline'],
              ['BulletedList', 'NumberedList', 'Blockquote'],
              ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'],
              ['Link', 'Unlink'],
              ['FontSize', 'TextColor'],
              ['Image'],
              ['Undo', 'Redo'],
              ['Source', 'Maximize'],
            ],
          };
        },
      },
    },
    methods: {
      deleteImage(src) {
        let data = new FormData();
        data.append('src', src);

        axios.post('/ckeditor-file-delete', data)
            .catch(err => console.log(err));
      },
    },
    watch: {
      value: function () {
        if (this.value != CKEDITOR.instances[this.modelName].getData()) {
          CKEDITOR.instances[this.modelName].setData(this.value);
        }
      }
    },
    mounted() {
      const config = {
        toolbar: this.options.toolbar,
        language: this.options.language,
        height: this.options.height,
        //extraPlugins: this.extraplugins
      };

      CKEDITOR.replace(this.$el/*, config*/);
      CKEDITOR.instances[this.modelName].setData(this.value);
      CKEDITOR.instances[this.modelName].on('change', () => {
        const value = CKEDITOR.instances[this.modelName].getData();
        if (value !== this.value) {
          this.$emit('input', value);
        }
      });


      let _this = this;
      CKEDITOR.instances[this.modelName].on('key', function (e) {
        if (+e.data.keyCode === 8 || +e.data.keyCode === 46) {

          let selectedRanges = '';
          let selectedImg = null;

          if(e.editor.getSelection()){
            selectedRanges = e.editor.getSelection().getRanges();
            selectedImg = e.editor.getSelection();
            selectedImg = selectedImg.getSelectedElement();
          }

          if (selectedImg && selectedImg.getName() === "img") {
            _this.deleteImage(selectedImg.$.src);
          }
          else if (selectedRanges.length) {
            for (let range of selectedRanges) {
              if (+e.data.keyCode === 8) {
                let previousRange = range.getPreviousEditableNode();
                if (previousRange && previousRange.getName && previousRange.getName() === 'img' && !range.checkStartOfBlock()) {
                  _this.deleteImage(previousRange.$.src);
                }
              } else if (+e.data.keyCode === 46) {
                let nextRange = range.getNextEditableNode();
                if (nextRange && nextRange.getName && nextRange.getName() === 'img' && !range.checkEndOfBlock()) {
                  _this.deleteImage(nextRange.$.src);
                }
              }

            }
          }
        }

      });


    },
    destroyed() {
      if (CKEDITOR.instances[this.modelName]) {
        CKEDITOR.instances[this.modelName].destroy();
      }
    },
  };
</script>
