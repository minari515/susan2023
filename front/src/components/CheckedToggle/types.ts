export interface CheckedToggleProps {
	checked: boolean;
	onChange: (event: React.ChangeEvent<HTMLInputElement>) => void;
	children: React.ReactNode;
}
