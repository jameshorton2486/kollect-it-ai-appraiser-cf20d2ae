
import { Button } from "@/components/ui/button";
import { CloudUpload, Wand2, Save, LoaderCircle } from "lucide-react";

interface ControlPanelProps {
  onPaste: () => void;
  onGenerate: () => void;
  onSave: () => void;
  imageExists: boolean;
  appraisalExists: boolean;
  isGenerating?: boolean;
}

export const ControlPanel = ({
  onPaste,
  onGenerate,
  onSave,
  imageExists,
  appraisalExists,
  isGenerating = false
}: ControlPanelProps) => {
  return (
    <div className="flex flex-wrap gap-3">
      <Button 
        variant="outline"
        onClick={onPaste}
      >
        <CloudUpload className="mr-2" />
        Paste Image (Ctrl+V)
      </Button>
      
      <Button 
        onClick={onGenerate}
        disabled={!imageExists || isGenerating}
      >
        {isGenerating ? (
          <LoaderCircle className="mr-2 animate-spin" />
        ) : (
          <Wand2 className="mr-2" />
        )}
        {isGenerating ? 'Generating...' : 'Generate Appraisal'}
      </Button>
      
      <Button 
        variant="secondary"
        onClick={onSave}
        disabled={!appraisalExists}
      >
        <Save className="mr-2" />
        Save Appraisal
      </Button>
    </div>
  );
};
