import type { FlexMessage } from "@line/bot-sdk";

const announceFlexMessage = (announceText: string): FlexMessage => ({
	type: "flex",
	altText: `${announceText}`,
	contents: {
		type: "bubble",
		size: "giga",
		header: {
			type: "box",
			layout: "vertical",
			contents: [
				{
					type: "box",
					layout: "vertical",
					contents: [
						{
							type: "text",
							text: "📢お知らせ",
							size: "lg",
							weight: "bold",
							align: "center",
							color: "#FFFFFF",
						},
						{
							type: "text",
							text: "Announce",
							size: "xxs",
							weight: "bold",
							align: "center",
							color: "#FFFFFF",
						},
					],
				},
			],
			backgroundColor: "#284275",
		},
		body: {
			type: "box",
			layout: "vertical",
			contents: [
				{
					type: "box",
					layout: "vertical",
					contents: [
						{
							type: "text",
							text: `${announceText}`,
							size: "lg",
							weight: "bold",
							wrap: true,
						},
					],
					paddingTop: "md",
				},
			],
			backgroundColor: "#FFFFFF",
		},
	},
});

export default announceFlexMessage;
