
import { Card } from "@/components/ui/card";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { ImageUploader } from "./ImageUploader";
import { AppraisalResults } from "./AppraisalResults";
import { ControlPanel } from "./ControlPanel";
import { useState } from "react";

export const AppraiserDashboard = () => {
  const [selectedImage, setSelectedImage] = useState<string | null>(null);
  const [appraisalResult, setAppraisalResult] = useState<string | null>(null);

  const handlePaste = () => {
    // Will be implemented later
    console.log("Paste image");
  };

  const handleGenerate = () => {
    // Will be implemented later
    console.log("Generate appraisal");
  };

  const handleSave = () => {
    // Will be implemented later
    console.log("Save appraisal");
  };

  return (
    <div className="container mx-auto py-8 px-4 min-w-[768px]">
      <h1 className="text-4xl font-bold mb-8 text-center">Kollect-It Expert Appraiser</h1>
      
      <Card className="p-6">
        <div className="mb-6">
          <ControlPanel 
            onPaste={handlePaste}
            onGenerate={handleGenerate}
            onSave={handleSave}
            imageExists={!!selectedImage}
            appraisalExists={!!appraisalResult}
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
                <ImageUploader 
                  onImageSelect={setSelectedImage}
                  onAppraisalComplete={setAppraisalResult}
                />
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
    </div>
  );
};
