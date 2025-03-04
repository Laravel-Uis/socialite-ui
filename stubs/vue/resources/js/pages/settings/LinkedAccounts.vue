<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import LinkedAccount from '@/components/LinkedAccount.vue';
import SocialiteProviderIcon from '@/components/SocialiteProviderIcon.vue';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import type { BreadcrumbItem, User } from '@/types';
import type { SocialiteUi } from '@/types/socialite-ui';
import { Head } from '@inertiajs/vue3';

interface Props {
   status?: string;
   auth: { user: User };
   socialiteUi: SocialiteUi;
}

const { status, auth, socialiteUi } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
   {
      title: 'Profile settings',
      href: '/settings/profile',
   },
];

const availableAccounts = socialiteUi.providers.filter(
   (provider) => !auth.user.social_accounts.some((socialAccount) => socialAccount.provider.id === provider.id),
);
</script>

<template>
   <AppLayout :breadcrumbs="breadcrumbs">
      <Head title="Linked Accounts" />

      <SettingsLayout>
         <!-- Linked accounts -->
         <div class="space-y-6">
            <HeadingSmall title="Linked Accounts" description="View and remove your currently linked accounts." />

            <div class="space-y-4 rounded-lg border border-destructive/10 bg-destructive/5 p-4">
               <div class="relative space-y-0.5 text-destructive/80">
                  <p class="text-sm">
                     If you feel any of your connected accounts have been compromised, you should disconnect them immediately and change your
                     password.
                  </p>
               </div>
            </div>

            <div v-if="status" class="mt-2 text-sm font-medium text-green-600">{{ status }}</div>

            <template v-for="socialAccount in auth.user.social_accounts" v-bind:key="socialAccount.id">
               <LinkedAccount :social-account="socialAccount" :socialite-ui="socialiteUi" />
            </template>
         </div>

         <div class="space-y-6">
            <HeadingSmall
               title="Link Account"
               description="Link your account to any of the following services to enable additional login options."
            />

            <div v-if="socialiteUi.error" class="text-sm font-medium text-red-600/80">
               {{ socialiteUi.error }}
            </div>

            <div v-if="!socialiteUi.hasPassword" class="space-y-4 rounded-lg border border-destructive/10 bg-destructive/5 p-4">
               <div class="relative space-y-0.5 text-destructive/80">
                  <p class="text-sm">Set a password to link new accounts.</p>
               </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
               <template v-for="provider in availableAccounts" v-bind:key="provider.id">
                  <div class="flex flex-col space-y-6 rounded-xl border p-4 md:space-y-4">
                     <div class="flex w-full justify-center py-2">
                        <SocialiteProviderIcon :provider="provider.id" class="mx-auto h-8 w-8 md:mx-0" />
                     </div>

                     <div class="flex w-full justify-end">
                        <TooltipProvider v-if="!socialiteUi.hasPassword">
                           <Tooltip :delay-duration="0">
                              <TooltipTrigger class="flex w-full">
                                 <Button variant="secondary" class="w-full" disabled> Link {{ provider.name }} </Button>
                              </TooltipTrigger>
                              <TooltipContent>
                                 <p>Set a password to link new accounts.</p>
                              </TooltipContent>
                           </Tooltip>
                        </TooltipProvider>

                        <Button v-else as-child variant="secondary" class="w-full">
                           <a :href="route('oauth.redirect', { provider: provider.id })"> Link {{ provider.name }} </a>
                        </Button>
                     </div>
                  </div>
               </template>
            </div>
         </div>
      </SettingsLayout>
   </AppLayout>
</template>
