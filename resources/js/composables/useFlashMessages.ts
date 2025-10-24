import { ref, watch, nextTick } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

interface FlashMessages {
  success?: string;
  error?: string;
  warning?: string;
  info?: string;
}

export function useFlashMessages() {
  const page = usePage();
  const shownFlashMessages = ref<Set<string>>(new Set());

  const showToast = (message: string, type: 'success' | 'error' | 'warning' | 'info') => {
    const messageKey = `${type}:${message}`;
    
    if (!shownFlashMessages.value.has(messageKey)) {
      shownFlashMessages.value.add(messageKey);
      
      nextTick(() => {
        switch (type) {
          case 'success':
            toast.success(message);
            break;
          case 'error':
            toast.error(message);
            break;
          case 'warning':
            toast.warning(message);
            break;
          case 'info':
            toast.info(message);
            break;
        }
        
        setTimeout(() => {
          shownFlashMessages.value.delete(messageKey);
        }, 1000);
      });
    }
  };

  watch(() => page.props.flash as FlashMessages | undefined, (newFlash) => {
    const currentFlash = newFlash as FlashMessages | undefined;
    
    if (currentFlash?.success) showToast(currentFlash.success, 'success');
    if (currentFlash?.error) showToast(currentFlash.error, 'error');
    if (currentFlash?.warning) showToast(currentFlash.warning, 'warning');
    if (currentFlash?.info) showToast(currentFlash.info, 'info');
  }, { deep: true, immediate: true });

  return {
    showToast,
    clearMessages: () => shownFlashMessages.value.clear()
  };
}