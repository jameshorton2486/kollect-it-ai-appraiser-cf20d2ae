
import axios from 'axios';
import { promptTemplates } from '@/data/promptTemplates';

export interface AppraisalResult {
  appraisalText: string;
  error?: string;
}

export const generateAppraisal = async (
  imageData: string,
  apiKey: string,
  templateId: string = 'standard'
): Promise<AppraisalResult> => {
  try {
    const base64Image = imageData.includes('base64,')
      ? imageData.split('base64,')[1]
      : imageData;
    
    const template = promptTemplates.find(t => t.id === templateId) || promptTemplates[0];
    
    const response = await axios.post(
      'https://api.openai.com/v1/chat/completions',
      {
        model: 'gpt-4o-mini',
        messages: [
          {
            role: 'user',
            content: [
              {
                type: 'text',
                text: template.text
              },
              {
                type: 'image_url',
                image_url: {
                  url: `data:image/jpeg;base64,${base64Image}`
                }
              }
            ]
          }
        ],
        max_tokens: 4000
      },
      {
        headers: {
          'Authorization': `Bearer ${apiKey}`,
          'Content-Type': 'application/json'
        },
        timeout: 60000
      }
    );
    
    return {
      appraisalText: response.data.choices[0].message.content
    };
  } catch (error) {
    console.error('API error:', error);
    return {
      appraisalText: '',
      error: error instanceof Error ? error.message : 'Unknown error occurred'
    };
  }
};
