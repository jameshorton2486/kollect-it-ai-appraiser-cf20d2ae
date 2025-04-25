import { useState } from "react";
import { showNotification } from "@/utils/notifications";

interface ContentContext {
  brand?: string;
  material?: string;
  period?: string;
  notes?: string;
}

interface GeneratedContent {
  title: string;
  description: string;
  priceRange: string;
}

export function useAIContentGenerator() {
  const [isLoading, setIsLoading] = useState(false);

  const generateContent = async (
    imageUrl: string, 
    apiKey: string, 
    context: ContentContext = {}
  ): Promise<GeneratedContent> => {
    setIsLoading(true);
    
    try {
      // Get only the base64 data part of the URL
      const base64Data = imageUrl.split(',')[1];
      
      // Create prompt with context information
      let contextPrompt = "";
      if (context.brand) contextPrompt += `Brand: ${context.brand}\n`;
      if (context.material) contextPrompt += `Material: ${context.material}\n`;
      if (context.period) contextPrompt += `Period/Era: ${context.period}\n`;
      if (context.notes) contextPrompt += `Additional Notes: ${context.notes}\n`;
      
      // Create the GPT-4 API request
      const response = await fetch("https://api.openai.com/v1/chat/completions", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Authorization": `Bearer ${apiKey}`
        },
        body: JSON.stringify({
          model: "gpt-4o-mini", // Using GPT-4o-mini which supports images
          messages: [
            {
              role: "system",
              content: `You are an expert e-commerce product writer specializing in antiques and vintage items.

Please analyze the image and generate the following:

1. PRODUCT TITLE:
   - Create a short, clear, professional product title
   - Focus on the product's type, style, material, and period if recognizable
   - Keep the title under 60 characters

2. PRODUCT DESCRIPTION:
   - Write a detailed product description suitable for a collector or buyer
   - Include details about material, craftsmanship, period/style, condition, and typical use
   - Write in a professional, lightly editorial tone
   - Keep between 150-200 words

3. VALUE ESTIMATE:
   - Provide a realistic sales price range for this item based on typical market trends
   - Base it on the visual condition and age if available
   - Format as a dollar range (e.g., "$125 - $175")

Format your response in JSON with the keys: "title", "description", and "priceRange".
Be accurate and realistic in your descriptions. Use a professional tone.
If you can't determine certain aspects from the image, focus on what you can see.`
            },
            {
              role: "user",
              content: [
                {
                  type: "text",
                  text: contextPrompt || "Please analyze this product image and generate content."
                },
                {
                  type: "image_url",
                  image_url: {
                    url: `data:image/jpeg;base64,${base64Data}`
                  }
                }
              ]
            }
          ],
          temperature: 0.7
        })
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.error?.message || "API request failed");
      }

      const data = await response.json();
      const content = data.choices[0]?.message?.content;
      
      if (!content) {
        throw new Error("No content returned from API");
      }

      try {
        let jsonStr = content;
        
        const jsonMatch = content.match(/```(?:json)?\s*([\s\S]*?)\s*```/);
        if (jsonMatch && jsonMatch[1]) {
          jsonStr = jsonMatch[1];
        }
        
        const result = JSON.parse(jsonStr);
        
        return {
          title: result.title || "Untitled Product",
          description: result.description || "No description available.",
          priceRange: result.priceRange || "Value unknown"
        };
      } catch (parseError) {
        console.error("Failed to parse AI response:", parseError);
        console.log("Raw content:", content);
        
        const titleMatch = content.match(/Title:\s*([^\n]+)/i);
        const descriptionMatch = content.match(/Description:\s*([^#]+)/is);
        const priceMatch = content.match(/Value Range:\s*([^\n]+)/i) || content.match(/Price Range:\s*([^\n]+)/i);
        
        return {
          title: titleMatch?.[1]?.trim() || "Untitled Product",
          description: descriptionMatch?.[1]?.trim() || "No description available.",
          priceRange: priceMatch?.[1]?.trim() || "Value unknown"
        };
      }
      
    } catch (error) {
      console.error("Error generating content:", error);
      throw error;
    } finally {
      setIsLoading(false);
    }
  };

  return {
    generateContent,
    isLoading
  };
}
