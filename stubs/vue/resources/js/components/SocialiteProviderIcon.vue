<script setup lang="ts">
import BitbucketIcon from '@/components/SocialiteProviderIcons/BitbucketIcon.vue';
import FacebookIcon from '@/components/SocialiteProviderIcons/FacebookIcon.vue';
import GitHubIcon from '@/components/SocialiteProviderIcons/GitHubIcon.vue';
import GitlabIcon from '@/components/SocialiteProviderIcons/GitlabIcon.vue';
import GoogleIcon from '@/components/SocialiteProviderIcons/GoogleIcon.vue';
import LinkedInIcon from '@/components/SocialiteProviderIcons/LinkedInIcon.vue';
import SlackIcon from '@/components/SocialiteProviderIcons/SlackIcon.vue';
import TwitchIcon from '@/components/SocialiteProviderIcons/TwitchIcon.vue';
import TwitterIcon from '@/components/SocialiteProviderIcons/TwitterIcon.vue';
import XIcon from '@/components/SocialiteProviderIcons/XIcon.vue';
import {type ProviderId} from '@/types/socialite-ui';

defineProps<{
    provider: ProviderId;
}>();
</script>

<template>
   <BitbucketIcon v-bind="$attrs" v-if="provider === 'bitbucket'"/>
   <FacebookIcon v-bind="$attrs" v-if="provider === 'facebook'"/>
   <GitHubIcon v-bind="$attrs" v-if="provider === 'github'"/>
   <GitlabIcon v-bind="$attrs" v-if="provider === 'gitlab'"/>
   <GoogleIcon v-bind="$attrs" v-if="provider === 'google'"/>
   <LinkedInIcon v-bind="$attrs" v-if="provider === 'linkedin' || provider === 'linkedin-openid'"/>
   <SlackIcon v-bind="$attrs" v-if="provider === 'slack' || provider === 'slack-openid'"/>
   <TwitchIcon v-bind="$attrs" v-if="provider === 'twitch'"/>
   <TwitterIcon v-bind="$attrs" v-if="provider === 'twitter' || provider === 'twitter-oauth-2'"/>
   <XIcon v-bind="$attrs" v-if="provider === 'x'"/>
</template>
