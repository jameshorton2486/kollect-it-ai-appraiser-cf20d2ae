
import { useState } from "react";
import { Card } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";
import { Textarea } from "@/components/ui/textarea";
import { ImageUploader } from "./ImageUploader";
import { ImagePreview } from "./ImagePreview";
import { useToast } from "@/hooks/use-toast";

interface AppraisalFormProps {
  onSubmit: (data: {
    title: string;
    description: string;
    image: string;
  }) => Promise<void>;
  isLoading?: boolean;
}

export const AppraisalForm = ({ onSubmit, isLoading = false }: AppraisalFormProps) => {
  const [title, setTitle] = useState("");
  const [description, setDescription] = useState("");
  const [imageData, setImageData] = useState<string | null>(null);
  const { toast } = useToast();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!imageData) {
      toast({
        title: "Missing Image",
        description: "Please upload or paste an image to continue",
        variant: "destructive",
      });
      return;
    }

    if (!title.trim()) {
      toast({
        title: "Missing Title",
        description: "Please provide a title for your item",
        variant: "destructive",
      });
      return;
    }

    await onSubmit({
      title,
      description,
      image: imageData,
    });
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      <Card className="p-6">
        <div className="space-y-4">
          <div>
            <Label htmlFor="title">Item Title</Label>
            <Input
              id="title"
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              placeholder="E.g., Antique Silver Tea Set"
              required
            />
          </div>

          <div>
            <Label htmlFor="description">Item Description</Label>
            <Textarea
              id="description"
              value={description}
              onChange={(e) => setDescription(e.target.value)}
              placeholder="Describe the item with as much detail as possible"
              rows={4}
            />
          </div>

          <div className="space-y-2">
            <Label>Item Image</Label>
            {imageData ? (
              <ImagePreview
                imageData={imageData}
                onRemove={() => setImageData(null)}
              />
            ) : (
              <ImageUploader onImageSelect={setImageData} />
            )}
          </div>

          <Button 
            type="submit" 
            className="w-full"
            disabled={isLoading || !imageData}
          >
            {isLoading ? "Generating Appraisal..." : "Generate Appraisal"}
          </Button>
        </div>
      </Card>
    </form>
  );
};
