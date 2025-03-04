import { type SocialiteUi as SocialiteUiType } from '@/types/socialite-ui';
import { cn } from '@/lib/utils';
import { SocialiteProviderIcon } from '@/components/socialite-provider-icon';
import InputError from '@/components/input-error';
import * as React from 'react';
import { Separator } from '@/components/ui/separator';

interface SocialiteUiProps extends React.ComponentProps<"div"> {
  socialiteUi: SocialiteUiType;
}

export default function Socialite({socialiteUi, ...props }: SocialiteUiProps) {
  return (
      <div className={cn('grid space-y-6', props.className)} {...props}>
          <div className="inline-flex items-center w-full">
              <Separator className="shrink" />
              <span className="text-muted-foreground text-center text-sm mx-3">OR</span>
              <Separator className="shrink" />
          </div>

          <InputError message={socialiteUi.error} className="text-center" />

          <div className="grid grid-cols-4 gap-3">
              {socialiteUi.providers.map((provider) => (
                  <a
                      key={provider.id}
                      className="inline-flex items-center justify-center rounded-md px-4 py-2 border hover:border-black transition duration-300 ease-out"
                      href={route('oauth.redirect', { provider: provider.id })}
                  >
                      <SocialiteProviderIcon provider={provider.id} className="h-6 w-6"/>
                  </a>
              ))}
          </div>
      </div>
  );
}
