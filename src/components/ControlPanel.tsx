
import { Button } from "@/components/ui/button";
import { CloudUpload, AutoFixHigh, SaveAlt } from "lucide-react";

interface ControlPanelProps {
  onPaste: () => void;
  onGenerate: () => void;
  onSave: () => void;
  imageExists: boolean;
  appraisalExists: boolean;
}

export const ControlPanel = ({
  onPaste,
  onGenerate,
  onSave,
  imageExists,
  appraisalExists
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
        disabled={!imageExists}
      >
        <AutoFixHigh className="mr-2" />
        Generate Appraisal
      </Button>
      
      <Button 
        variant="secondary"
        onClick={onSave}
        disabled={!appraisalExists}
      >
        <SaveAlt className="mr-2" />
        Save Appraisal
      </Button>
    </div>
  );
};
