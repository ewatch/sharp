<template>
    <Modal :visible.sync="visible" @ok="handleSubmitButtonClicked" @hidden="handleClosed">
        <transition>
            <Form
                v-if="visible"
                :props="form"
                independant
                ignore-authorizations
                style="transition-duration: 300ms"
                ref="form"
            />
        </transition>
    </Modal>
</template>

<script>
    import { Modal } from 'sharp-ui';
    import { Form } from 'sharp-form';

    export default {
        components: {
            Modal,
            Form,
        },
        props: {
            form: Object,
        },
        data() {
            return {
                visible: false
            }
        },
        watch: {
            form(form) {
                this.visible = !!form;
            }
        },
        methods: {
            submit(...args) {
                return this.$refs.form.submit(...args);
            },
            handleSubmitButtonClicked(e) {
                e.preventDefault();
                this.$emit('submit');
            },
            handleClosed() {
                this.$emit('close');
            }
        },
    }
</script>