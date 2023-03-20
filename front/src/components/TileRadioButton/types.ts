export interface TileRadioButtonProps {
	id: number;
	name: string;
	value: any;
	imageComponent?: JSX.Element;
	displayText: string;
	onChange: (event: React.ChangeEvent<HTMLInputElement>) => void;
	defaultCheckedValue?: any;
}
