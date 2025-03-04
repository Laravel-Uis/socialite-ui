// Components
import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler, useState } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';
import { type Provider } from '@/types/socialite-ui';

export default function ConfirmLinkAccount({ provider }: { provider: Provider }) {
    const [result, setResult] = useState<'confirm'|'deny'|undefined>(undefined);

    const { data, setData, post, processing, errors, reset } = useForm({
        password: '',
        provider: provider.id,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('oauth.confirm', { provider: provider.id, result }), {
            onFinish: () => reset('password'),
        });
    };

    return (
      <AuthLayout
        title={`Link ${provider.name}`}
        description={`Please confirm your password to connect your ${provider.name} account.`}
      >
          <Head title={`Link ${provider.name}`} />

          <form onSubmit={submit} className="flex flex-col gap-6">
              <div className="space-y-6">
                  <div className="grid gap-2">
                      <Label htmlFor="password">Password</Label>
                      <Input
                        id="password"
                        type="password"
                        name="password"
                        placeholder="Password"
                        autoComplete="current-password"
                        value={data.password}
                        autoFocus
                        onChange={(e) => setData('password', e.target.value)}
                      />

                      <InputError message={errors.password} />
                  </div>
              </div>

              <Button className="w-full" onClick={() => setResult('confirm')} disabled={processing}>
                  {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                  Link My Account
              </Button>

              <Button variant="destructive" className="w-full" onClick={() => setResult('deny')} disabled={processing}>
                  {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                  Cancel
              </Button>
          </form>
      </AuthLayout>
    );
}
