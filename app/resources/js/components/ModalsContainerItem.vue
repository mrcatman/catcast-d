<template>
  <c-modal
      :model-value="true"
      :header="modal.title"
      @update:model-value="close"
  >
    <template #main>
      <div class="standard-modal__text" v-if="!modal.component">
        {{ modal.text ?? (modal.confirm ? $t('global.are_you_sure') : null) }}
      </div>
      <c-form-v2
          v-else
          ref="form"
          modal
          :handler="save"
          :initial-values="modal.formValues"
          :show-submit="false"
          @success="close"
      >
        <template #default="{ values, errors }">
            <component
                ref="customComponent"
                :is="modal.component"
                v-bind="modal.props"
                :data="values"
                :values="values"
                :errors="errors"
            />
        </template>
      </c-form-v2>
    </template>

    <template #buttons v-if="modal.confirm">
      <c-button
          :disabled="buttonDisabled"
          :color="modal.buttonColor ?? 'red'"
          :loading="loading"
          @click="submit"
      >
        {{ modal.buttonText || $t('global.delete') }}
      </c-button>
      <c-button flat @click="close">
        {{ modal.cancelText || $t('global.cancel') }}
      </c-button>
    </template>
  </c-modal>
</template>
<style lang="scss">
.standard-modal {
  &__text {
    padding-bottom: 1em;
  }
}
</style>
<script lang="ts" setup>
import type { OpenedModal } from '@/stores/modals';

const props = defineProps<{
  modal: OpenedModal;
}>();

const { hideModal } = useModal();

const formRef = useTemplateRef('form');
const customComponentRef = useTemplateRef('customComponent');

const textLoading = ref(false);

const loading = computed(() => {
  return props.modal.component ? !!formRef.value?.loading : textLoading.value;
})

const buttonDisabled = computed(() => {
  if (!props.modal.buttonDisabledFn) {
    return false;
  }
  return props.modal.buttonDisabledFn(
      formRef.value?.values ?? props.modal.formValues ?? {},
      customComponentRef.value
  );
})

const close = () => {
  hideModal(props.modal.id);
}

const save = (values: Record<string, any>) => {
  return Promise.resolve(props.modal.fn?.(values, customComponentRef.value));
}

const submit = async () => {
  if (props.modal.component) {
    formRef.value?.submit();
    return;
  }

  textLoading.value = true;
  try {
    await save(props.modal.formValues ?? {});
    close();
  } finally {
    textLoading.value = false;
  }
}
</script>
