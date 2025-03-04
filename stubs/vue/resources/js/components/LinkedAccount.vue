<script setup lang="ts">
import type { SocialiteUi, SocialAccount } from '@/types/socialite-ui';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import SocialiteProviderIcon from '@/components/SocialiteProviderIcon.vue';
import UserInfo from '@/components/UserInfo.vue';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import {
   Dialog, DialogClose,
   DialogContent,
   DialogDescription,
   DialogFooter,
   DialogTitle,
   DialogTrigger
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';

interface Props {
   socialAccount: SocialAccount;
   socialiteUi: SocialiteUi;
}

const { socialAccount, socialiteUi } = defineProps<Props>();

const passwordInput = ref<HTMLInputElement | null>(null);

const updateAvatarForm = useForm({
   avatar: socialAccount.avatar,
});

const updateAvatar = () => {
   updateAvatarForm.patch(route('profile.avatar.update'), {
      onFinish: () => {
         updateAvatarForm.reset();
      },
   });
};

const unlinkAccountForm = useForm({
   password: '',
});

const unlinkAccount = (e: Event) => {
   e.preventDefault();

   unlinkAccountForm.delete(route('linked-accounts.destroy', { socialAccount }), {
      preserveScroll: true,
      onSuccess: () => closeModal(),
      onError: () => passwordInput.value?.focus(),
      onFinish: () => unlinkAccountForm.reset(),
   });
};

const closeModal = () => {
   unlinkAccountForm.clearErrors();
   unlinkAccountForm.reset();
};
</script>

<template>
   <div class="flex px-6 py-4 gap-4 border rounded-xl">
      <div class="grid w-full md:grid-cols-2 items-center gap-4">
         <div class="flex flex-row-reverse md:flex-row justify-between md:justify-start items-center gap-3">
            <SocialiteProviderIcon :provider="socialAccount.provider.id" class="h-8 w-8"/>

            <div class="flex items-center justify-center gap-2">
               <UserInfo :user="socialAccount" :showEmail="true" />
            </div>
         </div>

         <div class="flex flex-col md:flex-row items-center justify-between md:justify-end gap-3 md:gap-2">
            <form @submit.prevent="updateAvatar" class="flex w-full md:w-auto">
               <Button variant="link" :disabled="updateAvatarForm.processing" class="flex w-full">
                  Use Avatar
               </Button>
            </form>

            <TooltipProvider v-if="!socialiteUi.hasPassword">
               <Tooltip :delay-duration="0">
                  <TooltipTrigger class="w-full md:w-auto">
                     <Button variant="destructive" :disabled="!socialiteUi.hasPassword">
                        Unlink
                     </Button>
                  </TooltipTrigger>
                  <TooltipContent>
                     <p>Set a password to unlink this account</p>
                  </TooltipContent>
               </Tooltip>
            </TooltipProvider>

            <template v-if="socialAccount && socialiteUi.hasPassword">
               <Dialog>
                  <DialogTrigger asChild>
                     <Button variant="destructive" :disabled="! socialiteUi.hasPassword" class="w-full md:w-auto">
                        Unlink
                     </Button>
                  </DialogTrigger>
                  <DialogContent>
                     <DialogTitle>Are you sure you want to unlink this account?</DialogTitle>
                     <DialogDescription>Once unlinked, you will no longer be able to sign in with this socialAccount.</DialogDescription>

                     <form @submit.prevent="unlinkAccount">
                        <div class="space-y-6">
                           <div class="grid gap-2">
                              <Label htmlFor="password">Password</Label>
                              <Input
                                 id="password"
                                 type="password"
                                 class="mt-1 block w-full"
                                 v-model="unlinkAccountForm.password"
                                 required
                                 autocomplete="current-password"
                                 autofocus
                              />

                              <InputError :message="unlinkAccountForm.errors.password" />
                           </div>

                           <DialogFooter>
                              <DialogClose>
                                 <Button variant="link" :onclick="closeModal">
                                    Cancel
                                 </Button>
                              </DialogClose>

                              <Button variant="destructive" :disabled="unlinkAccountForm.processing" as-child>
                                 <button type="submit">Unlink Account</button>
                              </Button>
                           </DialogFooter>
                        </div>
                     </form>
                  </DialogContent>
               </Dialog>
            </template>
         </div>
      </div>
   </div>
</template>
