<template>
    <div>
        <loading v-model="show_loading"></loading>
        <v-stepper v-model="step">
            <v-stepper-header>
                <v-stepper-step :complete="step > 1" step="1">
                    Step 1: Product Information
                </v-stepper-step>

                <v-divider></v-divider>

                <v-stepper-step :complete="step > 2" step="2">
                    Step 2: Product Images
                </v-stepper-step>

                <v-divider></v-divider>

                <v-stepper-step step="3">
                    Step 3: Schedule
                </v-stepper-step>
            </v-stepper-header>

            <v-stepper-items>
                <!-- PRODUCT INFORMATION -->
                <v-stepper-content step="1">
                    <v-row justify="center">
                        <v-col cols="6" xs="12" md="8" sm="12" lg="6">
                            <v-card class="mb-3">
                                <v-card-text class="pa-3">
                                    <v-form
                                        data-vv-scope="product-info"
                                        class="pb-3"
                                    >
                                        <v-text-field
                                            label="Name"
                                            v-validate="'required'"
                                            :error-messages="
                                                errors.collect(
                                                    'product-info.name'
                                                )
                                            "
                                            data-vv-name="name"
                                            v-model="form.name"
                                            required
                                        ></v-text-field>

                                        <v-autocomplete
                                            label="Category"
                                            v-model="form.category_id"
                                            :items="categories"
                                            item-text="name"
                                            item-value="id"
                                            v-validate="'required'"
                                            :error-messages="
                                                errors.collect(
                                                    'product-info.category'
                                                )
                                            "
                                            data-vv-name="category"
                                            required
                                        ></v-autocomplete>

                                        <v-textarea
                                            label="Description"
                                            autocomplete="description"
                                            v-model="form.description"
                                            v-validate="'required'"
                                            :error-messages="
                                                errors.collect(
                                                    'product-info.description'
                                                )
                                            "
                                            data-vv-name="description"
                                            required
                                        ></v-textarea>
                                    </v-form>

                                    <v-btn
                                        color="primary"
                                        @click="validateStep(2)"
                                    >
                                        Next
                                    </v-btn>
                                    <v-btn text @click="cancel">
                                        Cancel
                                    </v-btn>
                                </v-card-text>
                            </v-card>
                        </v-col>
                    </v-row>
                </v-stepper-content>

                <!-- PRODUCT IMAGES -->
                <v-stepper-content step="2">
                    <v-row justify="center">
                        <v-col cols="6" xs="12" md="8" sm="12" lg="6">
                            <v-card class="mb-3">
                                <v-card-text class="pa-3">
                                    <v-form
                                        data-vv-scope="product-image"
                                        class="pb-4"
                                    >
                                        <ImagePicker
                                            v-model="images"
                                            :activeImageUploads="
                                                activeImageUploads
                                            "
                                        >
                                            <v-flex xs4 md3>
                                                <img
                                                    :src="placeholderImage"
                                                    width="100%"
                                                    height="100%"
                                                />
                                            </v-flex>
                                        </ImagePicker>
                                    </v-form>

                                    <!-- ACTIONS -->
                                    <v-btn
                                        color="primary"
                                        @click="validateStep(3)"
                                    >
                                        Next
                                    </v-btn>
                                    <v-btn text @click="step = 1">
                                        Back
                                    </v-btn>
                                </v-card-text>
                            </v-card>
                        </v-col>
                    </v-row>
                </v-stepper-content>

                <!-- SCHEDULE -->
                <v-stepper-content step="3">
                    <v-row justify="center">
                        <v-col cols="6" xs="12" md="8" sm="12" lg="6">
                            <v-card class="mb-3">
                                <v-card-text class="pa-3">
                                    <v-form
                                        data-vv-scope="product-schedule"
                                        class="pb-3"
                                    >
                                        <v-text-field
                                            label="Date and Time"
                                            v-validate="'required'"
                                            type="datetime-local"
                                            :error-messages="
                                                errors.collect(
                                                    'product-schedule.date time'
                                                )
                                            "
                                            data-vv-name="date time"
                                            v-model="form.date_time"
                                            required
                                        ></v-text-field>
                                    </v-form>

                                    <!-- ACTIONS -->
                                    <v-btn color="primary" @click="save">
                                        Submit
                                    </v-btn>
                                    <v-btn text @click="step = 2">
                                        Back
                                    </v-btn>
                                </v-card-text>
                            </v-card>
                        </v-col>
                    </v-row>
                </v-stepper-content>
            </v-stepper-items>
        </v-stepper>
    </div>
</template>

<script>
import loading from "@components/Loading.vue";
import { ImagePicker, imageUploadingStates } from "@nagoos/vue-image-picker";
import differenceby from "lodash.differenceby";
import TWEEN from "@tweenjs/tween.js";

export default {
    components: {
        ImagePicker,
        loading
    },
    data: () => ({
        step: 1,
        placeholderImage: "/img/default-image.png",
        form: {},
        categories: [],
        images: [],
        activeImageUploads: {},
        show_loading: false
    }),

    created() {
        let vm = this;
        vm.getCategory();
        window.requestAnimationFrame(() => vm.fakeUploadAnimate());
    },

    watch: {
        images(newImages, oldImages) {
            const newlyAddedImages = differenceby(
                newImages,
                oldImages,
                image => image.key
            );
            newlyAddedImages.forEach(image => this.fakeUpload(image.key));
        }
    },

    computed: {
        async valid() {
            let vm = this;
            return await vm.$validator.validateAll("product-info");
        },
        async valid2() {
            let vm = this;
            return (await vm.images.length) > 0;
        },
        async valid3() {
            let vm = this;
            return await vm.$validator.validateAll("product-schedule");
        }
    },

    methods: {
        async getCategory() {
            const { data } = await axios.get("/categories");
            this.categories = data;
        },

        // validating each step form
        async validateStep(step) {
            let vm = this;

            const next = () => {
                if (step == 0) {
                    vm.step = vm.step + 1;
                } else {
                    vm.step = step;
                }
            };
            if (vm.step == 1) {
                if (await vm.valid) {
                    if (step == 2 && (await vm.valid2) && (await vm.valid3)) {
                        next();
                    } else {
                        next();
                    }
                }
            } else if (vm.step == 2) {
                if (await vm.valid2) {
                    if (step == 3 && (await vm.valid3)) {
                        next();
                    } else {
                        next();
                    }
                }
                vm.$toast("Product image file is required", "warning");
            } else if (vm.step == 3) {
                if (await vm.valid3) {
                    next();
                }
            }
        },

        fakeUpload(key) {
            Vue.set(this.activeImageUploads, key, {
                progress: 0,
                state: imageUploadingStates.NEW
            });

            const imageUploadObj = { progress: 0 };
            const randomDelayTime = randomRange(100, 1500);
            const randomUploadTime = randomRange(1000, 3000);

            new TWEEN.Tween(imageUploadObj)
                .to({ progress: 100 }, randomUploadTime)
                .delay(randomDelayTime)
                .easing(TWEEN.Easing.Quadratic.In)
                .onStart(() => {
                    this.activeImageUploads[key].state =
                        imageUploadingStates.UPLOADING;
                })
                .onUpdate(({ progress }) => {
                    this.activeImageUploads[key].progress = progress;
                })
                .onComplete(() => {
                    this.activeImageUploads[key].state =
                        imageUploadingStates.COMPLETE;
                })
                .start();
        },

        fakeUploadAnimate() {
            // TWEEN.getAll().forEach(tween => tween.update());
            TWEEN.update();
            window.requestAnimationFrame(() => this.fakeUploadAnimate());
        },

        async save() {
            let vm = this;
            if (!(await vm.valid)) {
                vm.step = 1;
                return;
            } else if (vm.images.length == 0) {
                vm.step = 2;
                vm.$toast("Product image file is required", "warning");
                return;
            } else if (!(await vm.valid3)) {
                vm.step = 3;
                return;
            } else {
                vm.show_loading = true;
                vm.form.images = vm.images;
                const { data } = await axios.post("/products", vm.form);
                if (data[0] != "error") {
                    vm.$toast("Product successfully added!", "success");
                    // vm.eventBus.$emit("refresh_ssid");
                    vm.$router.push("/dashboard/products");
                } else {
                    vm.$toast(data[1], "error");
                    vm.show_loading = false;
                }
            }
        },

        cancel() {
            this.$router.go(-1);
        }
    }
};
</script>
