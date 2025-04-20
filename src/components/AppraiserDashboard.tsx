
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { ImageUploader } from "./ImageUploader";
import { AppraisalResults } from "./AppraisalResults";
import { useState } from "react";

export const AppraiserDashboard = () => {
  const [selectedImage, setSelectedImage] = useState<string | null>(null);
  const [appraisalResult, setAppraisalResult] = useState<string | null>(null);

  return (
    <div className="container mx-auto py-8 px-4">
      <h1 className="text-4xl font-bold mb-8 text-center">Kollect-It Expert Appraiser</h1>
      
      <div className="grid gap-8 md:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Image Upload</CardTitle>
            <CardDescription>
              Upload or paste an image of the item you want to appraise
            </CardDescription>
          </CardHeader>
          <CardContent>
            <ImageUploader 
              onImageSelect={setSelectedImage}
              onAppraisalComplete={setAppraisalResult}
            />
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Appraisal Results</CardTitle>
            <CardDescription>
              Professional appraisal report with detailed analysis
            </CardDescription>
          </CardHeader>
          <CardContent>
            <AppraisalResults result={appraisalResult} />
          </CardContent>
        </Card>
      </div>
    </div>
  );
};
