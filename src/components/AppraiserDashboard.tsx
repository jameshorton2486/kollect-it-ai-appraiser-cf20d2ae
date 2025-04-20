
import { useState, useEffect } from "react";
import { Card } from "@/components/ui/card";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { ImageUploader } from "./ImageUploader";
import { ImagePreview } from "./ImagePreview";
import { AppraisalResults } from "./AppraisalResults";
import { ControlPanel } from "./ControlPanel";
import { showNotification } from "@/utils/notifications";
import { useClipboardImage } from "@/hooks/useClipboardImage";
import { generateAppraisal } from "@/services/appraisalService";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";
import { Settings } from "lucide-react";
import { storeApiKey, getApiKey, hasApiKey } from "@/services/configService";
import { ApiSettings } from "./ApiSettings";

export const AppraiserDashboard = () => {
  const [selectedImage, setSelectedImage] = useState<string | null>(null);
  const [optimizedImage, setOptimizedImage] = useState<string | null>(null);
  const [appraisalResult, setAppraisalResult] = useState<string | null>(null);
  const [apiKey, setApiKey] = useState<string>('');
  const [isGenerating, setIsGenerating] = useState(false);
  const [showApiSettings, setShowApiSettings] = useState(false);
  const { image: pastedImage, handlePaste } = useClipboardImage();

  useEffect(() => {
    const savedApiKey = getApiKey();
    if (savedApiKey) {
      setApiKey(savedApiKey);
    }
  }, []);

  useEffect(() => {
    if (pastedImage) {
      setSelectedImage(pastedImage);
    }
  }, [pastedImage]);

  const handleApiKeyChange = (newApiKey: string) => {
    setApiKey(newApiKey);
  };

  const handleGenerate = async () => {
    if (!optimizedImage) {
      showNotification("Please wait for image processing to complete", "info");
      return;
    }
    
    if (!apiKey) {
      showNotification("Please enter your OpenAI API key", "error");
      return;
    }

    setIsGenerating(true);
    showNotification("Generating appraisal...", "info");

    try {
      const result = await generateAppraisal(optimizedImage, apiKey);
      
      if (result.error) {
        showNotification(result.error, "error");
      } else {
        setAppraisalResult(result.appraisalText);
        showNotification("Appraisal generated successfully!", "success");
      }
    } catch (error) {
      showNotification("Failed to generate appraisal", "error");
    } finally {
      setIsGenerating(false);
    }
  };

  const handleSave = () => {
    showNotification("Saving appraisal...", "info");
  };

  return (
    <div className="container mx-auto py-8 px-4 min-w-[768px]">
      <h1 className="text-4xl font-bold mb-8 text-center">Kollect-It Expert Appraiser</h1>
      
      <Card className="p-6">
        <div className="mb-6">
          <div className="mb-4 flex items-center justify-between">
            <div className="flex-1 max-w-md">
              <Label htmlFor="apiKey">OpenAI API Key</Label>
              <div className="flex gap-2">
                <Input
                  id="apiKey"
                  type="password"
                  value={apiKey}
                  onChange={(e) => storeApiKey(e.target.value)}
                  placeholder="Enter your OpenAI API key"
                />
                <Button
                  variant="outline"
                  size="icon"
                  onClick={() => setShowApiSettings(true)}
                >
                  <Settings className="h-4 w-4" />
                </Button>
              </div>
              <p className="text-sm text-muted-foreground mt-1">
                Note: For better security, consider using Supabase to store your API keys.
              </p>
            </div>
          </div>
          <ControlPanel 
            onPaste={handlePaste}
            onGenerate={handleGenerate}
            onSave={handleSave}
            imageExists={!!selectedImage}
            appraisalExists={!!appraisalResult}
            isGenerating={isGenerating}
          />
        </div>

        <Tabs defaultValue="upload" className="w-full">
          <TabsList className="grid w-full grid-cols-2">
            <TabsTrigger value="upload">Image Upload</TabsTrigger>
            <TabsTrigger value="results">Appraisal Results</TabsTrigger>
          </TabsList>
          
          <TabsContent value="upload" className="mt-6">
            <Card>
              <div className="p-6">
                <h2 className="text-2xl font-semibold mb-2">Upload Image</h2>
                <p className="text-muted-foreground mb-6">
                  Upload or paste an image of the item you want to appraise
                </p>
                <ImagePreview 
                  imageData={selectedImage} 
                  onOptimizedImage={setOptimizedImage}
                />
                <div className="mt-6">
                  <ImageUploader 
                    onImageSelect={setSelectedImage}
                    onAppraisalComplete={setAppraisalResult}
                  />
                </div>
              </div>
            </Card>
          </TabsContent>

          <TabsContent value="results" className="mt-6">
            <Card>
              <div className="p-6">
                <h2 className="text-2xl font-semibold mb-2">Appraisal Results</h2>
                <p className="text-muted-foreground mb-6">
                  Professional appraisal report with detailed analysis
                </p>
                <AppraisalResults result={appraisalResult} />
              </div>
            </Card>
          </TabsContent>
        </Tabs>
      </Card>

      <ApiSettings
        open={showApiSettings}
        onOpenChange={setShowApiSettings}
        onApiKeyChange={handleApiKeyChange}
      />
    </div>
  );
};
