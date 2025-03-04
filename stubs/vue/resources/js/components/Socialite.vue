<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Separator } from '@/components/ui/separator';
import SocialiteProviderIcon from '@/components/SocialiteProviderIcon.vue';
import type { SocialiteUi } from '@/types/socialite-ui';

defineProps<{
   socialiteUi: SocialiteUi;
}>();
</script>

<template>
   <div class="grid space-y-6">
      <div class="inline-flex w-full items-center">
         <Separator class="shrink" />
         <span class="mx-3 text-center text-sm text-muted-foreground">OR</span>
         <Separator class="shrink" />
      </div>

      <InputError :message="socialiteUi.error" class="text-center" />

      <div class="grid grid-cols-4 gap-3">
         <a
            v-for="provider in socialiteUi.providers"
            :key="provider.id"
            class="inline-flex items-center justify-center rounded-md px-4 py-2 border hover:border-black transition duration-300 ease-out"
            :href="route('oauth.redirect', { provider: provider.id })"
         >
            <SocialiteProviderIcon :provider="provider.id" class="h-6 w-6" />
         </a>
      </div>
   </div>
</template>
