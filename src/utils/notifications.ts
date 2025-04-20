
import { toast } from "@/components/ui/sonner"

type NotificationType = "success" | "error" | "info"

export const showNotification = (message: string, type: NotificationType = "info") => {
  toast(message, {
    duration: 6000,
  });
};
