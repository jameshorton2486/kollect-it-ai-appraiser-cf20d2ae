
import { useClipboardImage } from "@/hooks/useClipboardImage";
import { ImageIcon, Upload } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { useState } from "react";
import { showNotification } from "@/utils/notifications";

interface ImageUploaderProps {
  onImageSelect: (imageData: string) => void;
}

export const ImageUploader = ({ onImageSelect }: ImageUploaderProps) => {
  const [isDragging, setIsDragging] = useState(false);
  const { handlePaste } = useClipboardImage();

  const handleDragOver = (e: React.DragEvent<HTMLDivElement>) => {
    e.preventDefault();
    setIsDragging(true);
  };

  const handleDragLeave = (e: React.DragEvent<HTMLDivElement>) => {
    e.preventDefault();
    setIsDragging(false);
  };

  const handleDrop = async (e: React.DragEvent<HTMLDivElement>) => {
    e.preventDefault();
    setIsDragging(false);

    const files = Array.from(e.dataTransfer.files);
    const imageFile = files.find(file => file.type.startsWith('image/'));

    if (imageFile) {
      const reader = new FileReader();
      reader.onload = () => {
        const result = reader.result as string;
        onImageSelect(result);
        showNotification("Image uploaded successfully", "success");
      };
      reader.readAsDataURL(imageFile);
    }
  };

  const handleFileSelect = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = () => {
        const result = reader.result as string;
        onImageSelect(result);
        showNotification("Image uploaded successfully", "success");
      };
      reader.readAsDataURL(file);
    }
  };

  return (
    <Card
      className={`p-6 border-2 border-dashed transition-colors ${
        isDragging ? 'border-primary' : 'border-muted'
      }`}
      onDragOver={handleDragOver}
      onDragLeave={handleDragLeave}
      onDrop={handleDrop}
    >
      <div className="flex flex-col items-center justify-center gap-4">
        <ImageIcon className="h-12 w-12 text-muted-foreground" />
        <div className="text-center">
          <p className="text-sm text-muted-foreground mb-2">
            Drag and drop an image here, or click to select
          </p>
          <p className="text-xs text-muted-foreground">
            You can also paste an image from your clipboard
          </p>
        </div>
        <Button variant="secondary" asChild>
          <label>
            <input
              type="file"
              accept="image/*"
              className="hidden"
              onChange={handleFileSelect}
            />
            <Upload className="h-4 w-4 mr-2" />
            Select Image
          </label>
        </Button>
      </div>
    </Card>
  );
};
