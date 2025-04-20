
import React from 'react';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { storeApiKey, getApiKey, clearApiKey } from '@/services/configService';

interface ApiSettingsProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  onApiKeyChange: (apiKey: string) => void;
}

export const ApiSettings = ({ open, onOpenChange, onApiKeyChange }: ApiSettingsProps) => {
  const [apiKey, setApiKey] = React.useState(getApiKey);

  const handleSave = () => {
    storeApiKey(apiKey);
    onApiKeyChange(apiKey);
    onOpenChange(false);
  };

  const handleClear = () => {
    clearApiKey();
    setApiKey('');
    onApiKeyChange('');
  };

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>API Settings</DialogTitle>
          <DialogDescription>
            Configure your OpenAI API key for image appraisals. For better security, consider using Supabase.
          </DialogDescription>
        </DialogHeader>
        <div className="grid gap-4 py-4">
          <div className="grid gap-2">
            <Label htmlFor="apiKey">OpenAI API Key</Label>
            <Input
              id="apiKey"
              type="password"
              value={apiKey}
              onChange={(e) => setApiKey(e.target.value)}
              placeholder="Enter your OpenAI API key"
            />
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" onClick={handleClear}>Clear Key</Button>
          <Button onClick={handleSave}>Save Changes</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
};
