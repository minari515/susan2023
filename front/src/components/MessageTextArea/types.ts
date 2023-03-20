import { Dispatch, SetStateAction } from "react";

export interface MessageTextAreaProps {
	text: string;
	setText: Dispatch<SetStateAction<string>>; //React HooksのsetStateを使う
}
