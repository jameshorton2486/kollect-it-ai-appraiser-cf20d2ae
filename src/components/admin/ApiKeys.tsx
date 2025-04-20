
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { useToast } from "@/hooks/use-toast";
import { useState } from "react";
import { getApiKey, storeApiKey } from "@/services/configService";

export const ApiKeys = () => {
  const [apiKey, setApiKey] = useState(getApiKey);
  const { toast } = useToast();

  const handleSave = () => {
    storeApiKey(apiKey);
    toast({
      title: "API Key Saved",
      description: "Your OpenAI API key has been saved successfully.",
    });
  };

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-2xl font-bold mb-4">API Key Configuration</h2>
        <p className="text-muted-foreground mb-4">
          Configure your OpenAI API key for the Expert Appraiser AI system.
        </p>
      </div>

      <Card className="p-6">
        <div className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor="apiKey">OpenAI API Key</Label>
            <Input
              id="apiKey"
              type="password"
              value={apiKey}
              onChange={(e) => setApiKey(e.target.value)}
              placeholder="Enter your OpenAI API key"
            />
          </div>
          <Button onClick={handleSave}>Save API Key</Button>
        </div>
      </Card>
    </div>
  );
};
