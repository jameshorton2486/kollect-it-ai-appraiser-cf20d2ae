
import React from "react";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { PromptTemplate } from "@/data/promptTemplates";

interface TemplateSelectorProps {
  templates: PromptTemplate[];
  selectedTemplateId: string;
  onSelectTemplate: (template: PromptTemplate) => void;
}

export const TemplateSelector = ({
  templates,
  selectedTemplateId,
  onSelectTemplate,
}: TemplateSelectorProps) => {
  const handleValueChange = (value: string) => {
    const template = templates.find((t) => t.id === value);
    if (template) {
      onSelectTemplate(template);
    }
  };

  const selectedTemplate = templates.find((t) => t.id === selectedTemplateId);

  return (
    <div className="space-y-2">
      <Select value={selectedTemplateId} onValueChange={handleValueChange}>
        <SelectTrigger className="w-full">
          <SelectValue placeholder="Select appraisal type" />
        </SelectTrigger>
        <SelectContent>
          {templates.map((template) => (
            <SelectItem key={template.id} value={template.id}>
              {template.name}
            </SelectItem>
          ))}
        </SelectContent>
      </Select>
      {selectedTemplate && (
        <p className="text-sm text-muted-foreground">
          {selectedTemplate.description}
        </p>
      )}
    </div>
  );
};
