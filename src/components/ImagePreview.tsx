
import React from 'react';
import { Card } from "@/components/ui/card";
import { ImageIcon } from "lucide-react";

interface ImagePreviewProps {
  imageData: string | null;
}

export const ImagePreview: React.FC<ImagePreviewProps> = ({ imageData }) => {
  if (!imageData) {
    return (
      <Card className="h-[400px] flex items-center justify-center bg-muted">
        <div className="text-center space-y-4">
          <ImageIcon className="w-12 h-12 mx-auto text-muted-foreground" />
          <p className="text-muted-foreground">
            Paste an image (Ctrl+V) to begin
          </p>
        </div>
      </Card>
    );
  }
  
  return (
    <Card className="overflow-hidden">
      <div className="max-h-[600px] overflow-auto text-center p-4">
        <img 
          src={imageData} 
          alt="Item to appraise" 
          className="max-w-full max-h-[600px] object-contain mx-auto"
        />
      </div>
    </Card>
  );
};
