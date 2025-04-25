
import { useState, useCallback } from "react";
import { Button } from "@/components/ui/button";
import { Upload } from "lucide-react";

interface ImageUploaderProps {
  onImageSelect: (imageData: string) => void;
  onAppraisalComplete: (result: string) => void;
  onImagesProcessed?: (images: Array<{id: string, processed: string, name: string}>) => void;
  maxImages?: number;
}

export const ImageUploader = ({ onImageSelect, onAppraisalComplete, onImagesProcessed, maxImages = 15 }: ImageUploaderProps) => {
  const [isDragging, setIsDragging] = useState(false);

  const handleDrop = useCallback((e: React.DragEvent) => {
    e.preventDefault();
    setIsDragging(false);
    
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) {
      const reader = new FileReader();
      reader.onload = () => {
        onImageSelect(reader.result as string);
        // TODO: Implement API call to get appraisal
        onAppraisalComplete("Sample appraisal result. API integration pending.");
      };
      reader.readAsDataURL(file);
    }
  }, [onImageSelect, onAppraisalComplete]);

  const handleDragOver = useCallback((e: React.DragEvent) => {
    e.preventDefault();
    setIsDragging(true);
  }, []);

  const handleDragLeave = useCallback((e: React.DragEvent) => {
    e.preventDefault();
    setIsDragging(false);
  }, []);

  const handleFileSelect = useCallback((e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file && file.type.startsWith('image/')) {
      const reader = new FileReader();
      reader.onload = () => {
        onImageSelect(reader.result as string);
        // TODO: Implement API call to get appraisal
        onAppraisalComplete("Sample appraisal result. API integration pending.");
      };
      reader.readAsDataURL(file);
    }
  }, [onImageSelect, onAppraisalComplete]);

  return (
    <div
      className={`border-2 border-dashed rounded-lg p-8 text-center ${
        isDragging ? 'border-primary bg-primary/10' : 'border-gray-300'
      }`}
      onDrop={handleDrop}
      onDragOver={handleDragOver}
      onDragLeave={handleDragLeave}
    >
      <Upload className="mx-auto h-12 w-12 text-gray-400" />
      <p className="mt-4 text-sm text-gray-600">
        Drag and drop an image here, or
      </p>
      <div className="mt-4">
        <label htmlFor="file-upload">
          <input
            id="file-upload"
            type="file"
            className="hidden"
            accept="image/*"
            onChange={handleFileSelect}
          />
          <Button
            variant="outline"
            onClick={() => document.getElementById('file-upload')?.click()}
          >
            Select Image
          </Button>
        </label>
      </div>
    </div>
  );
};
