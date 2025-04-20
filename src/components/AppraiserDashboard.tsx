
import { useState } from "react";
import { Card } from "@/components/ui/card";
import { AppraisalForm } from "./AppraisalForm";
import { AppraisalResults } from "./AppraisalResults";
import { showNotification } from "@/utils/notifications";
import { generateAppraisal } from "@/services/appraisalService";
import { useClipboardImage } from "@/hooks/useClipboardImage";

export const AppraiserDashboard = () => {
  const [appraisalResult, setAppraisalResult] = useState<string | null>(null);
  const [isGenerating, setIsGenerating] = useState(false);
  const { handlePaste } = useClipboardImage();

  const handleAppraisalSubmit = async (data: {
    title: string;
    description: string;
    image: string;
  }) => {
    setIsGenerating(true);
    showNotification("Generating appraisal...", "info");

    try {
      const result = await generateAppraisal(data.image);
      
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

  return (
    <div className="container mx-auto py-8 px-4">
      <h1 className="text-4xl font-bold mb-8 text-center">Expert Appraiser AI</h1>
      
      <div className="max-w-3xl mx-auto space-y-6">
        <AppraisalForm
          onSubmit={handleAppraisalSubmit}
          isLoading={isGenerating}
        />
        
        {appraisalResult && (
          <Card className="p-6">
            <AppraisalResults result={appraisalResult} />
          </Card>
        )}
      </div>
    </div>
  );
};
