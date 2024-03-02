<template>
    <div class="file-upload">
        <div class="upload-form-widget">
            <input type="file" ref="fileUpload" class="input-file" :name="name" :disabled="isSaving" multiple @change="fileChange($event)">
            <div class="text" v-if="isInitial">
                {{ trans('fileupload.upload_files') }}
            </div>
            <div class="text" v-if="isSaving">
                {{ trans('fileupload.uploading_files', {filesCount: filesCount}) }}
            </div>
        </div>
        <div class="file-upload-files">
            <draggable v-model="files.thumbsOrder" class="saved-files-list" v-if="files.thumbsOrder.length" @start="drag=true"
                       @end="drag=false">
                <div class="item" v-for="thumb in files.thumbsOrder">
                    <div class="saved-file-edit" @click="isEditableToggle(thumb.id)"><span class="glyphicons glyphicons-edit"></span></div>
                    <div class="saved-file-delete" @click="deleteThumb(thumb.id)">x</div>
                    <img class="thumb" :src="thumb.thumb_path" :title="thumb.alt">
                    <input v-if="thumbsIsEdit[thumb.id]" type="text" class="thumb-name" v-model="files.thumbsNames[thumb.id]">
                </div>
            </draggable>
            <div class="files-list">
                <div class="item" :class="{spinner: isSaving}" v-for="(newFile, index) in files.files">
                    <div class="new-file-delete" @click="deleteFile(index)">x</div>
                    <img class="thumb" :src="filesUrls[index] || filePic" :title="newFile.name">
                    <input type="text" class="file-name" v-model="files.filesNames[index]">
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import draggable from 'vuedraggable'

    const STATUS_INITIAL = 0, STATUS_SAVING = 1;

    Vue.prototype.trans = (key, values) => {
        return values ? Lang.get(key, values) : Lang.get(key);
    };
    Vue.prototype.getLocale = () => {
        return Lang.getLocale();
    };
    Vue.prototype.trans_choice = (key, num, values) => {
        function getLastDigitsForTrans(num) {
            let numStr = num.toString();
            if (numStr.length === 1) {
                return num;
            }
            else if (numStr.length > 2) {
                numStr = numStr.substr(-2, 2);
            }
            return numStr.substr(-2, 1) === '1' ? parseInt(numStr) : parseInt(numStr.substr(-1));
        }
        let frmNum = getLastDigitsForTrans(num);
        return values ? Lang.choice(key, frmNum, values) : Lang.choice(key, frmNum);
    };

    export default {
        name: 'ElFileUpload',

        componentName: 'ElFileUpload',

        model: {
            prop: 'value',
            event: 'files-update'
        },
        props: {
            value: {required: true},
            name: {required: true,},
            quantity: {required: true},
            thumbs: {required: true},
            deleteUrl: {required: true},
            errors: {
                required: false,
                default: null,
                type: Object
            },
            filePic: {
                required: false,
                type: String,
                default: '/vendor/fileupload/images/file.svg'
            },
            loading: {
                required: false,
                type: Boolean,
                default: false
            }
        },
        data() {
            return {
                files: {
                    files: [],
                    filesNames: [],
                    thumbsOrder: this.thumbs,
                    thumbsNames: {}
                },
                filesUrls: [],
                currentStatus: null,
                thumbsIsEdit: {}

            }
        },
        computed: {
            isInitial() {
                return this.currentStatus === STATUS_INITIAL && !this.loading;
            },
            isSaving() {
                return this.filesCount > 0 && (this.currentStatus === STATUS_SAVING || this.loading);
            },
            filesCount() {
                return this.files.files.length;
            }
        },
        components: {
            draggable
        },
        watch: {
            thumbs: {
                handler: function (value) {
                    Vue.set(this.files, 'thumbsOrder', value);
                },
                deep: true
            },
            files: {
                handler: function (value) {
                    if (value.length === 0) {
                        Vue.set(this, 'filesUrls', []);
                    }
                    this.$emit('files-update', value);
                },
                deep: true
            },
            loading: function (value) {
                if (value) {
                    Vue.set(this, 'thumbsIsEdit', {});
                }
            }
        },
        methods: {
            reset(thumbs) {
                // reset form to initial state
                this.currentStatus = STATUS_INITIAL;
                if (thumbs) {
                    this.files.thumbsOrder = thumbs;
                }
                this.files.files = [];
                this.files.filesNames = [];
                for (let thumb in this.files.thumbsOrder) {
                    let id = this.files.thumbsOrder[thumb].id;
                    this.files.thumbsNames[id] = this.files.thumbsOrder[thumb].alt;
                }
            },
            clearErrors() {
              if (this.errors) {
                  this.errors.clear(this.name);
				  this.$emit('clear-error', this.name);
              }
            },
            isEditableToggle(id) {
                Vue.set(this.thumbsIsEdit, id, !(this.thumbsIsEdit.hasOwnProperty(id) && this.thumbsIsEdit[id]));
            },
            deleteThumb(fileid) {
                if (!confirm(Lang.get('fileupload.delete_confirmation'))) {
                    return false;
                }
                if (fileid) {
                    axios.post(this.deleteUrl, {fileId: fileid})
                        .then(() => {
                            for (let i = 0, thumb; thumb = this.files.thumbsOrder[i]; i++) {
                                if (thumb.id === fileid) {
                                    Vue.delete(this.files.thumbsOrder, i);
                                    Vue.delete(this.files.thumbsNames, thumb.id)
                                }
                            }
                            this.clearErrors();
                        });
                }
            },
            deleteFile(index) {
                Vue.delete(this.files.files, index);
                Vue.delete(this.files.filesNames, index);
                Vue.delete(this.filesUrls, index);
                this.clearErrors();
            },
            fileChange(event) {
                this.currentStatus = STATUS_SAVING;
                for (let file of event.currentTarget.files) {
                    if ((this.files.thumbsOrder.length + this.files.files.length) >= parseInt(this.quantity)) {
                        alert(Lang.get('fileupload.max_images') + ' ' + this.quantity);
                    } else {
                        this.files.filesNames.push('');
                        this.files.files.push(file);
                        this.filePreview(file);
                    }
                }
                this.clearErrors();
                this.currentStatus = STATUS_INITIAL;
                this.$refs.fileUpload.value = '';
            },
            filePreview(file) {
                let reader = new FileReader();
                reader.onload = e => {
                    let thumbSrc = this.filePic;
                    if (/image/.test(file.type)) {
                        thumbSrc = e.target.result;
                    }
                    this.filesUrls.push(thumbSrc);
                };
                // Read in the image file as a data URL.
                reader.readAsDataURL(file);
            }
        },
        mounted() {
            this.reset();
        }
    }
</script>